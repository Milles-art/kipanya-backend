<?php
namespace App\Services\Cart;
use App\Enums\Commerce\CartStatus;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\User;
use App\Models\Wear\WearProductVariant;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
final class CartService { public const HEADER='X-Guest-Cart-Token'; public function current(?User $user,?string $guestToken):Cart{if($user){return Cart::firstOrCreate(['user_id'=>$user->id,'status'=>CartStatus::Active]);}// SECURITY: a guest token is a bearer secret and the browser generates it. Accept it only
        // if it is a well-formed, high-entropy 256-bit value; anything weak or malformed is
        // ignored and the server issues a fresh token instead, so a guessable cart id can never
        // be chosen by (or planted on) a client.
        $guestToken=$this->isStrongToken($guestToken)?$guestToken:null;
        $cart=null;
        if($guestToken!==null){$cart=Cart::query()->where('guest_token_hash',hash('sha256',$guestToken))->first();}
        if($cart&&($cart->status!==CartStatus::Active||($cart->expires_at!==null&&$cart->expires_at->isPast()))){$cart->delete();$cart=null;}
        if($cart===null){
            // Cart rows are cheap to create and never expire on their own: cap creation per IP.
            if(!RateLimiter::attempt('guest-cart-create:'.request()->ip(),100,static fn()=>true,3600)){throw new ThrottleRequestsException('Too many carts created. Please try again later.');}
            $guestToken??=bin2hex(random_bytes(32));
            $cart=Cart::create(['guest_token_hash'=>hash('sha256',$guestToken),'status'=>CartStatus::Active,'expires_at'=>now()->addDays(30)]);
        }
        $cart->setAttribute('_guest_cart_token',$guestToken);return $cart;}
    private function isStrongToken(?string $token):bool{return is_string($token)&&preg_match('/\A[a-f0-9]{64}\z/',$token)===1&&count(array_unique(str_split($token)))>=8;}
     public function add(Cart $cart,WearProductVariant $variant,int $quantity):Cart{$guestToken=$cart->getAttribute('_guest_cart_token');if($quantity<1)throw ValidationException::withMessages(['quantity'=>'Quantity must be at least 1.']);return DB::transaction(function()use($cart,$variant,$quantity,$guestToken):Cart{$variant=WearProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();$variant->loadMissing('product');if(!$variant->product||!$variant->product->is_active)throw ValidationException::withMessages(['variant'=>'Product is unavailable.']);$item=CartItem::firstOrNew(['cart_id'=>$cart->id,'wear_product_variant_id'=>$variant->id]);$new=$item->exists?$item->quantity+$quantity:$quantity;if($variant->stock<$new)throw ValidationException::withMessages(['quantity'=>'Requested quantity exceeds available stock.']);$item->quantity=$new;$item->save();$fresh=$cart->fresh('items.variant.product');if(!$fresh->user_id&&$guestToken){$fresh->setAttribute('_guest_cart_token',$guestToken);}return $fresh;});} public function set(Cart $cart,WearProductVariant $variant,int $quantity):Cart{return DB::transaction(function()use($cart,$variant,$quantity):Cart{$variant=WearProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();$item=$cart->items()->where('wear_product_variant_id',$variant->id)->firstOrFail();if($quantity<=0){$item->delete();return $cart->fresh('items.variant.product');}if($variant->stock<$quantity)throw ValidationException::withMessages(['quantity'=>'Requested quantity exceeds available stock.']);$item->update(['quantity'=>$quantity]);return $cart->fresh('items.variant.product');});} public function remove(Cart $cart,WearProductVariant $variant):Cart{$cart->items()->where('wear_product_variant_id',$variant->id)->delete();return $cart->fresh('items.variant.product');} public function clear(Cart $cart):Cart{$cart->items()->delete();return $cart->fresh('items.variant.product');} public function merge(Cart $guest,User $user):Cart{$target=$this->current($user,null);if($guest->id===$target->id)return $target->fresh('items.variant.product');foreach($guest->items()->with('variant.product')->lockForUpdate()->get() as $item){$variant=$item->variant;if(!$variant||!$variant->product?->is_active)continue;$existing=$target->items()->where('wear_product_variant_id',$item->wear_product_variant_id)->first();$quantity=min(($existing?->quantity??0)+$item->quantity,(int)$variant->stock);if($quantity<1){$existing?->delete();continue;}if($existing)$existing->update(['quantity'=>$quantity]);else $target->items()->create(['wear_product_variant_id'=>$item->wear_product_variant_id,'quantity'=>$quantity]);}$guest->delete();return $target->fresh('items.variant.product');} }

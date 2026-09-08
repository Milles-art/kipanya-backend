<?php
namespace App\Actions\Cart;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
final class MergeGuestCart { public function __construct(private readonly CartService $cartService){} public function execute(string $guestToken,User $user):Cart{return DB::transaction(function()use($guestToken,$user){$guest=Cart::query()->where('guest_token_hash',hash('sha256',$guestToken))->where('status','active')->with('items.variant.product')->lockForUpdate()->first();return $guest?$this->cartService->merge($guest,$user):$this->cartService->current($user,null)->load('items.variant.product');});} }

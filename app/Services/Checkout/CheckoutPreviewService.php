<?php
namespace App\Services\Checkout;
use App\Models\Cart\Cart;
use Illuminate\Validation\ValidationException;
final class CheckoutPreviewService {
 public function preview(Cart $cart): array {
  $cart->load('items.variant.product');
  if($cart->items->isEmpty()) throw ValidationException::withMessages(['cart'=>'Your cart is empty.']);
  $items=[]; $subtotal=0.0;
  foreach($cart->items as $item){$variant=$item->variant;$product=$variant?->product;if(!$variant||!$product||!$product->is_active)throw ValidationException::withMessages(['cart'=>'One or more items are no longer available.']);if($variant->stock<$item->quantity)throw ValidationException::withMessages(['cart'=>"Stock changed for {$product->name}. Please review your cart."]);$unit=(float)$product->price;$line=$unit*$item->quantity;$subtotal+=$line;$items[]=['variant_id'=>$variant->id,'product_id'=>$product->id,'quantity'=>$item->quantity,'unit_price'=>$unit,'line_total'=>$line];}
  return ['currency'=>'TZS','items'=>$items,'subtotal'=>round($subtotal,2),'delivery_fee'=>0.0,'total'=>round($subtotal,2)];
 }
}

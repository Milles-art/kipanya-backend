<?php
namespace App\Http\Resources\Api\V1;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CartResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'guest_cart_token'=>$this->when(is_null($this->user_id), $this->getAttribute('_guest_cart_token')),'status'=>$this->status->value,'items'=>$this->items->map(fn($i)=>['id'=>$i->id,'variant_id'=>$i->wear_product_variant_id,'quantity'=>$i->quantity,'unit_price'=>(float)$i->variant->product->price,'line_total'=>(float)$i->variant->product->price*$i->quantity,'variant'=>['size'=>$i->variant->size,'color'=>$i->variant->color,'sku'=>$i->variant->sku],'product'=>['id'=>$i->variant->product->id,'name'=>$i->variant->product->name,'slug'=>$i->variant->product->slug,'image'=>$i->variant->product->image_url]]),'subtotal'=>(float)$this->items->sum(fn($i)=>(float)$i->variant->product->price*$i->quantity),'item_count'=>(int)$this->items->sum('quantity')];}}

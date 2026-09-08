<?php
namespace App\Http\Controllers\Api\V1\Commerce;
use App\Http\Controllers\Controller;
use App\Models\Cart\WishlistItem;
use App\Models\Wear\WearProduct;
use Illuminate\Http\Request;
class WishlistController extends Controller { public function index(Request $r){return response()->json(['data'=>$r->user()->wearWishlist()->with('product')->latest()->get()]);} public function store(Request $r){$d=$r->validate(['product_id'=>['required','integer','exists:wear_products,id']]);$product=WearProduct::findOrFail($d['product_id']);abort_unless($product->is_active,404);$item=WishlistItem::firstOrCreate(['user_id'=>$r->user()->id,'wear_product_id'=>$product->id]);return response()->json(['data'=>$item->load('product')],201);} public function destroy(Request $r,WearProduct $product){$r->user()->wearWishlist()->where('wear_product_id',$product->id)->delete();return response()->noContent();} }

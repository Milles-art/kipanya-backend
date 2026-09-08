<?php
namespace App\Models\Cart;
use App\Models\Wear\WearProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CartItem extends Model { use HasFactory; protected $fillable=['cart_id','wear_product_variant_id','quantity']; protected function casts():array{return ['quantity'=>'integer'];} public function cart():BelongsTo{return $this->belongsTo(Cart::class);} public function variant():BelongsTo{return $this->belongsTo(WearProductVariant::class,'wear_product_variant_id');} }

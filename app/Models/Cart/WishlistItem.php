<?php
namespace App\Models\Cart;
use App\Models\User;
use App\Models\Wear\WearProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WishlistItem extends Model { protected $fillable=['user_id','wear_product_id']; public function user():BelongsTo{return $this->belongsTo(User::class);} public function product():BelongsTo{return $this->belongsTo(WearProduct::class,'wear_product_id');} }

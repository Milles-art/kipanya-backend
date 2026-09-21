<?php
namespace App\Models\Wear;
use App\Models\User; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class WearInventoryMovement extends Model { protected $fillable=['wear_product_variant_id','quantity','stock_before','stock_after','reason','notes','created_by']; protected $casts=['quantity'=>'integer','stock_before'=>'integer','stock_after'=>'integer']; public function variant():BelongsTo{return $this->belongsTo(WearProductVariant::class,'wear_product_variant_id');} public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} }

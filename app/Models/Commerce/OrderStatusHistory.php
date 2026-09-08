<?php
namespace App\Models\Commerce;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrderStatusHistory extends Model { protected $table='wear_order_status_history'; protected $fillable=['wear_order_id','from_status','to_status','reason','changed_by']; public function order():BelongsTo{return $this->belongsTo(WearOrder::class,'wear_order_id');} public function changedBy():BelongsTo{return $this->belongsTo(User::class,'changed_by');} }

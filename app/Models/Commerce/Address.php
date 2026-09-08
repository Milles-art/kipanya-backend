<?php
namespace App\Models\Commerce;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Address extends Model { protected $fillable=['user_id','type','label','recipient_name','phone','region','district','ward','street','notes','is_default']; protected function casts():array{return ['is_default'=>'boolean'];} public function user():BelongsTo{return $this->belongsTo(User::class);} }

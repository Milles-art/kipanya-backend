<?php
namespace App\Models\Commerce;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Address extends Model { protected $fillable=['user_id','type','label','recipient_name','phone','region','district','ward','street','notes','is_default']; protected function casts():array{return ['is_default'=>'boolean',
        // Delivery-contact PII is ciphertext at rest. Lookups go through the
        // deterministic phone_hash / recipient_name_hash blind indexes.
        'phone'=>'encrypted','recipient_name'=>'encrypted'];}
    /**
     * Keep the blind indexes in step with the encrypted columns on every write.
     */
    protected static function booted(): void
    {
        static::saving(function (self $address): void {
            $key = (string) config('app.key');

            // Normalize to E.164 first so providers (Selcom MSISDN) always get
            // a valid number and the blind index is computed over one form.
            // Validation guarantees parseability on request paths.
            if ($address->isDirty('phone') && $address->phone !== null) {
                $address->phone = \App\Support\PhoneNumber::normalize((string) $address->phone)->value();
            }

            if ($address->isDirty('phone')) {
                $plain = $address->phone;
                $address->phone_hash = $plain === null ? null : hash_hmac('sha256', (string) $plain, $key);
            }

            if ($address->isDirty('recipient_name')) {
                $plain = $address->recipient_name;
                $address->recipient_name_hash = $plain === null ? null : hash_hmac('sha256', strtolower((string) $plain), $key);
            }
        });
    }
    public static function phoneHash(string $phone): string { return hash_hmac('sha256', $phone, (string) config('app.key')); }
    public static function recipientNameHash(string $name): string { return hash_hmac('sha256', strtolower($name), (string) config('app.key')); }
    public function scopeWherePhone(Builder $query, string $phone): Builder { return $query->where('phone_hash', self::phoneHash($phone)); }
 public function user():BelongsTo{return $this->belongsTo(User::class);} }

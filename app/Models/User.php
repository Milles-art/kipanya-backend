<?php

namespace App\Models;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Role;
use App\Models\Auth\NotificationPreference;
use App\Models\Auth\UserProfile;
use App\Models\Cart\Cart;
use App\Models\Cart\WishlistItem;
use App\Models\Commerce\Address;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Security-sensitive attributes (`status`, `role`) are intentionally NOT
     * mass-assignable. Assign them explicitly so validated request data can
     * never escalate an account.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function wearWishlist(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin || $this->hasRole('super_admin');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', static fn ($query) => $query->where('slug', $permission))
            ->exists();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }
}

<?php

namespace App\Models;

use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
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

    /**
     * Roles that grant access to the admin surface. Using an explicit
     * allow-list (instead of "any role other than `user`") means a future
     * non-staff role can never silently gain admin access.
     */
    public const ADMIN_ROLE_SLUGS = ['super_admin', 'commerce_manager', 'support'];

    public function isAdmin(): bool
    {
        return $this->roles()->whereIn('slug', self::ADMIN_ROLE_SLUGS)->exists();
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
        // The legacy `role`/`status` columns are intentionally un-cast. Accept
        // both the raw string and the UserStatus enum so an in-memory model
        // built with the enum (e.g. a factory or freshly created row) is
        // treated the same as one hydrated from the database.
        $status = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;

        return $status === UserStatus::Active->value;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }
}

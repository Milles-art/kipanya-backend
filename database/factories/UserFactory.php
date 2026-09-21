<?php

namespace Database\Factories;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+255'.fake()->unique()->numerify('7########'),
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a fully-privileged administrator. Permission checks are driven by
     * the roles/permissions pivot, so the super_admin role is attached
     * explicitly rather than inferred from the legacy users.role column.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'status' => 'active',
        ])->afterCreating(function (User $user): void {
            $superAdmin = Role::query()->where('slug', 'super_admin')->first();

            if ($superAdmin) {
                $user->roles()->syncWithoutDetaching([$superAdmin->id]);
            }
        });
    }
}

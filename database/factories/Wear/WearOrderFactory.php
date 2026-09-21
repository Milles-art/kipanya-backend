<?php

namespace Database\Factories\Wear;

use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WearOrder>
 */
final class WearOrderFactory extends Factory
{
    protected $model = WearOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 300000);
        $deliveryFee = fake()->numberBetween(0, 15000);

        return [
            'order_number' => 'KP-'.fake()->unique()->numerify('######-??????'),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '+255'.fake()->unique()->numerify('7########'),
            'customer_email' => fake()->safeEmail(),
            'delivery_address' => fake()->address(),
            'delivery_city' => 'Dar es Salaam',
            'notes' => null,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'mobile_money',
            'placed_at' => now(),
        ];
    }
}

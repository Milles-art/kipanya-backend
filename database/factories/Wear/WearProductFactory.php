<?php

namespace Database\Factories\Wear;

use App\Models\Wear\WearProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WearProduct>
 */
final class WearProductFactory extends Factory
{
    protected $model = WearProduct::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->paragraph(),
            'category' => fake()->randomElement(['Hoodies', 'Long Sleeves', 'T-Shirts', 'Shirts', 'Polos']),
            'price' => fake()->randomFloat(2, 15000, 150000),
            'compare_at_price' => null,
            'image_path' => null,
            'badge' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}

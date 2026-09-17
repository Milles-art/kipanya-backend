<?php

namespace Database\Factories\Wear;

use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WearProductVariant> */
class WearProductVariantFactory extends Factory
{
    protected $model = WearProductVariant::class;

    public function definition(): array
    {
        return [
            'wear_product_id' => WearProduct::factory(),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['Black', 'White', 'Red']),
            'stock' => fake()->numberBetween(0, 50),
            'sku' => strtoupper(fake()->unique()->bothify('KP-????-####')),
        ];
    }
}

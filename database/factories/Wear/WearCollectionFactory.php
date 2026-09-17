<?php

namespace Database\Factories\Wear;

use App\Models\Wear\WearCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WearCollection>
 */
final class WearCollectionFactory extends Factory
{
    protected $model = WearCollection::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->optional()->sentence(),
            'cover_path' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}

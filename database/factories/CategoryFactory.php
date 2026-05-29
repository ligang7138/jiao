<?php

namespace Database\Factories;

use App\Models\Goods\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goods\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'pid' => 0,
            'sort' => $this->faker->numberBetween(0, 100),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function child(?int $parentId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'pid' => $parentId ?? Category::factory(),
        ]);
    }
}
<?php

namespace Database\Factories;

use App\Models\Goods\Goods;
use App\Models\Goods\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goods\Goods>
 */
class GoodsFactory extends Factory
{
    protected $model = Goods::class;

    public function definition(): array
    {
        return [
            'goods_name' => $this->faker->word(),
            'goods_sn' => 'GN' . $this->faker->unique()->numerify('######'),
            'cate_id' => 1,
            'scate_id' => 1,
            'unit' => $this->faker->randomElement(['斤', '公斤', '个', '包', '箱']),
            'spec' => $this->faker->randomElement(['500g', '1kg', '2kg', '5kg']),
            'level' => 1,
            'attr' => 1,
            'goods_type' => 0,
            'goods_channel' => 0,
            'discount_rate' => $this->faker->randomFloat(4, 0, 1),
            'status' => $this->faker->randomElement([Goods::STATUS_OFF, Goods::STATUS_ON]),
            'brand' => $this->faker->company(),
            'place' => $this->faker->city(),
            'add_time' => time(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Goods::STATUS_ON,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Goods::STATUS_OFF,
        ]);
    }
}
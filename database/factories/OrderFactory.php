<?php

namespace Database\Factories;

use App\Models\Order\Order;
use App\Models\School\School;
use App\Models\School\Canteen;
use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_sn' => 'ORD' . date('Ymd') . $this->faker->unique()->numerify('######'),
            'school_id' => School::factory(),
            'canteen_id' => Canteen::factory(),
            'supp_id' => Supplier::factory(),
            'send_date' => $this->faker->dateTimeBetween('+1 day', '+7 days')->format('Y-m-d'),
            'total_price' => $this->faker->randomFloat(2, 100, 10000),
            'status' => $this->faker->randomElement([
                Order::STATUS_DRAFT,
                Order::STATUS_PENDING,
                Order::STATUS_APPROVED,
                Order::STATUS_SHIPPED,
                Order::STATUS_RECEIVED,
            ]),
            'remark' => $this->faker->sentence(),
            'audit_status' => 0,
            'audit_time' => null,
            'add_time' => time(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DRAFT,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_APPROVED,
            'audit_status' => 1,
            'audit_time' => time(),
        ]);
    }
}
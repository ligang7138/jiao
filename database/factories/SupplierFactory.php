<?php

namespace Database\Factories;

use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => 'SUP' . $this->faker->unique()->numerify('######'),
            'company' => $this->faker->company(),
            'linkman' => $this->faker->name(),
            'mobile' => $this->faker->phoneNumber(),
            'status' => Supplier::STATUS_ENABLED,
            'add_time' => time(),
            'update_time' => time(),
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => [
            'status' => Supplier::STATUS_DISABLED,
        ]);
    }

    public function enabled(): static
    {
        return $this->state(fn () => [
            'status' => Supplier::STATUS_ENABLED,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\School\Canteen;
use App\Models\School\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School\Canteen>
 */
class CanteenFactory extends Factory
{
    protected $model = Canteen::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->randomElement(['第一食堂', '第二食堂', '教工食堂', '学生食堂']),
            'canteen_type' => $this->faker->randomElement([1, 2]),
            'linkman' => $this->faker->name(),
            'mobile' => $this->faker->phoneNumber(),
            'status' => 1,
            'add_time' => time(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
<?php

namespace Database\Factories;

use App\Models\School\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School\School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'school_name' => $this->faker->company() . '学校',
            'school_sn' => 'SCH' . $this->faker->unique()->numerify('######'),
            'school_district' => $this->faker->city(),
            'status' => 1,
            'add_time' => time(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
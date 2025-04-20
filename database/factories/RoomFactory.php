<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;
use App\Models\Manager;
use App\Models\Floor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        $creatorTypes = [
            Admin::class,
            Manager::class,
        ];
        $createdByType = $this->faker->randomElement($creatorTypes);
        $createdById = $createdByType::inRandomOrder()->first()?->id ?? Admin::factory();

        return [
            'name' => $this->faker->unique()->word() . ' Room',
            'number' => $this->faker->unique()->numberBetween(100, 999),
            'created_by_type' => $createdByType,
            'created_by_id' => $createdById,
            'floor_id' => Floor::inRandomOrder()->first()?->id ?? Floor::factory(),
            'price' => $this->faker->numberBetween(10000, 999999),
        ];
    }
}

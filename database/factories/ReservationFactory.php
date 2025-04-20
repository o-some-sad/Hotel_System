<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;
use App\Models\Manager;
use App\Models\Receptionist;
use App\Models\Client;
use App\Models\Room;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $creatorTypes = [
            Admin::class,
            Manager::class,
            Receptionist::class,
            Client::class
        ];
        $createdByType = $this->faker->randomElement($creatorTypes);
        $createdById = $createdByType::inRandomOrder()->first()?->id ?? Admin::factory();

        return [
            'client_id' => Client::inRandomOrder()->first()?->id ?? Client::factory(),
            'room_id' => Room::inRandomOrder()->first()?->id ?? Room::factory(),
            'created_by_type' => $createdByType,
            'created_by_id' => $createdById,
            'price' => $this->faker->numberBetween(100, 1000),
            'accompanying_number' => $this->faker->numberBetween(0, 5),
            'is_approved' => $this->faker->boolean(),
        ];
    }
}

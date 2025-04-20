<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use \Database\Seeders\UserSeeder;
// use \Database\Seeders\AdminSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ClientSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(ManagerSeeder::class);
        $this->call(ReceptionistSeeder::class);
        $this->call(FloorSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(ReservationsSeeder::class);
        $this->call(BanSeeder::class);
    }
}

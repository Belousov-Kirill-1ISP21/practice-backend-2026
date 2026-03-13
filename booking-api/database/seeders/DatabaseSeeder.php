<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            AirportSeeder::class,
            AircraftSeeder::class,
            FlightSeeder::class,
            BookingSeeder::class,    
            PassengerSeeder::class,  
            ReviewSeeder::class,
        ]);
    }
}
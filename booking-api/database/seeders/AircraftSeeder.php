<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aircraft;

class AircraftSeeder extends Seeder
{
    public function run()
    {
        $aircrafts = [
            [
                'model' => 'Boeing 737-800',
                'manufacturer' => 'Boeing',
                'total_seats' => 189
            ],
            [
                'model' => 'Airbus A320',
                'manufacturer' => 'Airbus',
                'total_seats' => 180
            ],
            [
                'model' => 'Sukhoi Superjet 100',
                'manufacturer' => 'Sukhoi',
                'total_seats' => 108
            ],
        ];
        
        foreach ($aircrafts as $aircraft) {
            Aircraft::firstOrCreate($aircraft);
        }
    }
}
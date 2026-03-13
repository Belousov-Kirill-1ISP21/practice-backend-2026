<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airport;

class AirportSeeder extends Seeder
{
    public function run()
    {
        $airports = [
            ['code' => 'MOW', 'name' => 'Sheremetyevo', 'city' => 'Moscow', 'country' => 'Russia', 'timezone' => 'Europe/Moscow'],
            ['code' => 'LED', 'name' => 'Pulkovo', 'city' => 'Saint Petersburg', 'country' => 'Russia', 'timezone' => 'Europe/Moscow'],
            ['code' => 'KZN', 'name' => 'Kazan', 'city' => 'Kazan', 'country' => 'Russia', 'timezone' => 'Europe/Moscow'],
            ['code' => 'AER', 'name' => 'Sochi', 'city' => 'Sochi', 'country' => 'Russia', 'timezone' => 'Europe/Moscow'],
        ];
        
        foreach ($airports as $airport) {
            Airport::firstOrCreate($airport);
        }
    }
}
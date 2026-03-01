<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Airport;

class AirportSeeder extends Seeder
{
    public function run()
    {
        $airports = [
            ['code' => 'MOW', 'name' => 'Шереметьево', 'city' => 'Москва', 'country' => 'Россия', 'timezone' => 'Europe/Moscow'],
            ['code' => 'LED', 'name' => 'Пулково', 'city' => 'Санкт-Петербург', 'country' => 'Россия', 'timezone' => 'Europe/Moscow'],
            ['code' => 'KZN', 'name' => 'Казань', 'city' => 'Казань', 'country' => 'Россия', 'timezone' => 'Europe/Moscow'],
        ];
        
        foreach ($airports as $airport) {
            Airport::create($airport);
        }
    }
}
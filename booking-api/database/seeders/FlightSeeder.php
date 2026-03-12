<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    public function run()
    {
        $flights = [
            [
                'flight_number' => 'SU100',
                'origin_airport_id' => 1, 
                'dest_airport_id' => 2,   
                'aircraft_id' => 1,
                'departure_time' => Carbon::parse('2026-12-25 10:00:00'),
                'arrival_time' => Carbon::parse('2026-12-25 11:30:00'),
                'base_price' => 5000,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU101',
                'origin_airport_id' => 2, 
                'dest_airport_id' => 1,   
                'aircraft_id' => 2,
                'departure_time' => Carbon::parse('2026-12-25 14:00:00'),
                'arrival_time' => Carbon::parse('2026-12-25 15:30:00'),
                'base_price' => 5500,
                'status' => 'scheduled'
            ],
            
            [
                'flight_number' => 'SU202',
                'origin_airport_id' => 1,
                'dest_airport_id' => 4,    
                'aircraft_id' => 3,
                'departure_time' => Carbon::parse('2026-12-25 09:00:00'),
                'arrival_time' => Carbon::parse('2026-12-25 12:00:00'),
                'base_price' => 4500,
                'status' => 'scheduled'
            ],
            
            [
                'flight_number' => 'SU303',
                'origin_airport_id' => 1, 
                'dest_airport_id' => 2,   
                'aircraft_id' => 1,
                'departure_time' => Carbon::parse('2026-03-12 10:00:00'),
                'arrival_time' => Carbon::parse('2026-03-12 11:30:00'),
                'base_price' => 5000,
                'status' => 'scheduled'
            ],
            
            [
                'flight_number' => 'SU404',
                'origin_airport_id' => 1,
                'dest_airport_id' => 4,    
                'aircraft_id' => 2,
                'departure_time' => Carbon::parse('2026-04-01 10:00:00'),
                'arrival_time' => Carbon::parse('2026-04-01 13:00:00'),
                'base_price' => 7500,
                'status' => 'scheduled'
            ],
        ];
        
        foreach ($flights as $flight) {
            Flight::create($flight);
        }
    }
}
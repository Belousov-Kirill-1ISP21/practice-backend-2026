<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Aircraft;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    public function run()
    {
        $mow = Airport::where('code', 'MOW')->first();
        $led = Airport::where('code', 'LED')->first();
        $kzn = Airport::where('code', 'KZN')->first();
        $aer = Airport::where('code', 'AER')->first();
        
        $b737 = Aircraft::where('model', 'Boeing 737-800')->first();
        $a320 = Aircraft::where('model', 'Airbus A320')->first();
        $ssj100 = Aircraft::where('model', 'Sukhoi Superjet 100')->first();
        
        $flights = [
            [
                'flight_number' => 'SU100',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $led->id,
                'aircraft_id' => $b737->id,
                'departure_time' => Carbon::parse('2026-03-12 10:00:00'),
                'arrival_time' => Carbon::parse('2026-03-12 11:30:00'),
                'base_price' => 5000,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU101',
                'origin_airport_id' => $led->id,
                'dest_airport_id' => $mow->id,
                'aircraft_id' => $a320->id,
                'departure_time' => Carbon::parse('2026-03-12 14:00:00'),
                'arrival_time' => Carbon::parse('2026-03-12 15:30:00'),
                'base_price' => 5500,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU102',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $kzn->id,
                'aircraft_id' => $ssj100->id,
                'departure_time' => Carbon::parse('2026-03-12 08:00:00'),
                'arrival_time' => Carbon::parse('2026-03-12 10:00:00'),
                'base_price' => 4000,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU103',
                'origin_airport_id' => $kzn->id,
                'dest_airport_id' => $aer->id,
                'aircraft_id' => $b737->id,
                'departure_time' => Carbon::parse('2026-03-12 16:00:00'),
                'arrival_time' => Carbon::parse('2026-03-12 18:30:00'),
                'base_price' => 6000,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU200',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $led->id,
                'aircraft_id' => $a320->id,
                'departure_time' => Carbon::parse('2026-03-13 09:00:00'),
                'arrival_time' => Carbon::parse('2026-03-13 10:30:00'),
                'base_price' => 4800,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU201',
                'origin_airport_id' => $led->id,
                'dest_airport_id' => $aer->id,
                'aircraft_id' => $ssj100->id,
                'departure_time' => Carbon::parse('2026-03-13 11:00:00'),
                'arrival_time' => Carbon::parse('2026-03-13 14:00:00'),
                'base_price' => 6500,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU202',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $kzn->id,
                'aircraft_id' => $b737->id,
                'departure_time' => Carbon::parse('2026-03-13 13:00:00'),
                'arrival_time' => Carbon::parse('2026-03-13 15:00:00'),
                'base_price' => 4200,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU203',
                'origin_airport_id' => $aer->id,
                'dest_airport_id' => $mow->id,
                'aircraft_id' => $a320->id,
                'departure_time' => Carbon::parse('2026-03-13 15:00:00'),
                'arrival_time' => Carbon::parse('2026-03-13 18:00:00'),
                'base_price' => 7000,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU204',
                'origin_airport_id' => $kzn->id,
                'dest_airport_id' => $led->id,
                'aircraft_id' => $ssj100->id,
                'departure_time' => Carbon::parse('2026-03-13 17:00:00'),
                'arrival_time' => Carbon::parse('2026-03-13 19:00:00'),
                'base_price' => 5200,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU300',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $aer->id,
                'aircraft_id' => $b737->id,
                'departure_time' => Carbon::parse('2026-04-01 10:00:00'),
                'arrival_time' => Carbon::parse('2026-04-01 13:00:00'),
                'base_price' => 7500,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU301',
                'origin_airport_id' => $aer->id,
                'dest_airport_id' => $mow->id,
                'aircraft_id' => $a320->id,
                'departure_time' => Carbon::parse('2026-04-01 14:00:00'),
                'arrival_time' => Carbon::parse('2026-04-01 17:00:00'),
                'base_price' => 7800,
                'status' => 'scheduled'
            ],
            [
                'flight_number' => 'SU999',
                'origin_airport_id' => $mow->id,
                'dest_airport_id' => $led->id,
                'aircraft_id' => $b737->id,
                'departure_time' => Carbon::now()->subDays(1),
                'arrival_time' => Carbon::now()->subDays(1)->addHours(2),
                'base_price' => 5000,
                'status' => 'arrived'
            ]
        ];
        
        foreach ($flights as $flight) {
            Flight::firstOrCreate(
                ['flight_number' => $flight['flight_number']],
                $flight
            );
        }
    }
}
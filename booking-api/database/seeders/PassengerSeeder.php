<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Passenger;
use Carbon\Carbon;

class PassengerSeeder extends Seeder
{
    public function run()
    {
        $passengers = [
            [
                'booking_id' => 1,
                'last_name' => 'Иванов',
                'first_name' => 'Иван',
                'middle_name' => 'Иванович',
                'birth_date' => '1990-05-15',
                'passport_number' => '1234567890',
                'seat_number' => '12A',
                'ticket_price' => 5000.00
            ],
            [
                'booking_id' => 1,
                'last_name' => 'Петрова',
                'first_name' => 'Мария',
                'middle_name' => 'Сергеевна',
                'birth_date' => '1992-08-22',
                'passport_number' => '0987654321',
                'seat_number' => '12B',
                'ticket_price' => 5000.00
            ],
            [
                'booking_id' => 2,
                'last_name' => 'Сидоров',
                'first_name' => 'Петр',
                'middle_name' => null,
                'birth_date' => '1988-11-03',
                'passport_number' => '555666777',
                'seat_number' => '14C',
                'ticket_price' => 5500.00
            ],
        ];
        
        foreach ($passengers as $passenger) {
            Passenger::firstOrCreate($passenger);
        }
    }
}
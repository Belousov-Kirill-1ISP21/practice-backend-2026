<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Flight;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'user@example.com')->first();
        $admin = User::where('email', 'admin@example.com')->first();
        
        $flight1 = Flight::where('flight_number', 'SU100')->first();
        $flight2 = Flight::where('flight_number', 'SU101')->first();
        $flight3 = Flight::where('flight_number', 'SU102')->first();
        $flight999 = Flight::where('flight_number', 'SU999')->first();
        
        $bookings = [
            [
                'user_id' => $user->id,
                'flight_id' => $flight1->id,
                'code' => 'ABC123',
                'status' => 'confirmed',
                'expires_at' => Carbon::now()->addDays(5),
                'total_price' => 10000.00,
                'payment_status' => 'paid'
            ],
            [
                'user_id' => $user->id,
                'flight_id' => $flight2->id,
                'code' => 'XYZ789',
                'status' => 'pending',
                'expires_at' => Carbon::now()->addHours(2),
                'total_price' => 5500.00,
                'payment_status' => 'pending'
            ],
            [
                'user_id' => $admin->id,
                'flight_id' => $flight3->id,
                'code' => 'QWE456',
                'status' => 'cancelled',
                'expires_at' => Carbon::now()->subDays(1),
                'total_price' => 4500.00,
                'payment_status' => 'refunded'
            ],
            [
                'user_id' => $admin->id,
                'flight_id' => $flight3->id,
                'code' => 'ADM789',
                'status' => 'pending',
                'expires_at' => Carbon::now()->addHours(3),
                'total_price' => 4500.00,
                'payment_status' => 'pending'
            ],
            [
                'user_id' => $user->id,
                'flight_id' => $flight999->id,
                'code' => 'REV123',
                'status' => 'confirmed',
                'expires_at' => Carbon::now()->addDays(5),
                'total_price' => 5000.00,
                'payment_status' => 'paid'
            ]
        ];
        
        foreach ($bookings as $booking) {
            Booking::firstOrCreate(
                ['code' => $booking['code']],
                $booking
            );
        }
    }
}
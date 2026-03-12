<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $bookings = [
            [
                'user_id' => 2, 
                'flight_id' => 1,
                'code' => 'ABC123',
                'status' => 'confirmed',
                'expires_at' => Carbon::now()->addDays(5),
                'total_price' => 10000.00,
                'payment_status' => 'paid'
            ],
            [
                'user_id' => 2,
                'flight_id' => 2, 
                'code' => 'XYZ789',
                'status' => 'pending',
                'expires_at' => Carbon::now()->addHours(2),
                'total_price' => 5500.00,
                'payment_status' => 'pending'
            ],
            [
                'user_id' => 1, 
                'flight_id' => 3,
                'code' => 'QWE456',
                'status' => 'cancelled',
                'expires_at' => Carbon::now()->subDays(1),
                'total_price' => 4500.00,
                'payment_status' => 'refunded'
            ],
            [
                'user_id' => 1, 
                'flight_id' => 3, 
                'code' => 'ADM789',
                'status' => 'pending',
                'expires_at' => Carbon::now()->addHours(3),
                'total_price' => 4500.00,
                'payment_status' => 'pending'
            ],
        ];
        
        foreach ($bookings as $booking) {
            Booking::create($booking);
        }
    }
}
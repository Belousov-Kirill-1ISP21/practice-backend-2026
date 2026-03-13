<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Booking;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $reviews = [
            [
                'booking_id' => 1,
                'rating' => 5,
                'comment' => 'Excellent flight, very comfortable'
            ],
            [
                'booking_id' => 2, 
                'rating' => 4,
                'comment' => 'Good service, slightly delayed'
            ],
            [
                'booking_id' => 3, 
                'rating' => 5,
                'comment' => 'Amazing experience, great crew'
            ],
            [
                'booking_id' => 4, 
                'rating' => 3,
                'comment' => 'Okay, but food could be better'
            ],
        ];

        foreach ($reviews as $reviewData) {
            $booking = Booking::find($reviewData['booking_id']);
            
            if ($booking) {
                Review::firstOrCreate([
                    'user_id' => $booking->user_id,
                    'flight_id' => $booking->flight_id,
                    'booking_id' => $reviewData['booking_id'],
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment']
                ]);
            }
        }
    }
}
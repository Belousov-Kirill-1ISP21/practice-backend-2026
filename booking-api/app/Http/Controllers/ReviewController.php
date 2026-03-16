<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReviewController extends Controller
{
    /**
     * POST /flights/{id}/reviews - Добавить отзыв
     */
    public function store(Request $request, $flightId)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $booking = Booking::where('user_id', auth()->id())
            ->where('id', $validated['booking_id'])
            ->where('flight_id', $flightId)
            ->first();

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        if ($booking->status !== 'confirmed') {
            abort(409, 'Отзыв можно оставить только после подтвержденного бронирования');
        }

        if ($booking->flight->status !== 'arrived') {
            abort(409, 'Отзыв можно оставить только после завершения рейса (статус arrived)');
        }

        if ($booking->review) {
            abort(409, 'Вы уже оставили отзыв на этот рейс');
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'flight_id' => $flightId,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Отзыв добавлен'
        ], 201);
    }

    /**
     * GET /flights/{id}/reviews - Получить все отзывы на рейс
     */
    public function index($flightId)
    {
        $reviews = Review::with('user:id,first_name,last_name')
            ->where('flight_id', $flightId)
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->count(),
                'reviews' => $reviews->map(function($review) {
                    return [
                        'id' => $review->id,
                        'user_name' => $review->user->first_name . ' ' . $review->user->last_name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at
                    ];
                })
            ]
        ], 200);
    }
}
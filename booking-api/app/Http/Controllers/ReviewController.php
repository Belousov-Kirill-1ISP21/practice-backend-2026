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
        try {
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
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Бронирование не найдено'
                ], 404);
            }

            if ($booking->status !== 'confirmed') {
                return response()->json([
                    'error' => 'CANNOT_REVIEW',
                    'message' => 'Отзыв можно оставить только после подтвержденного бронирования'
                ], 409);
            }

            if ($booking->flight->status !== 'arrived') {
                return response()->json([
                    'error' => 'FLIGHT_NOT_COMPLETED',
                    'message' => 'Отзыв можно оставить только после завершения рейса (статус arrived)'
                ], 409);
            }

            if ($booking->review) {
                return response()->json([
                    'error' => 'ALREADY_REVIEWED',
                    'message' => 'Вы уже оставили отзыв на этот рейс'
                ], 409);
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Review store error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }

    /**
     * GET /flights/{id}/reviews - Получить все отзывы на рейс
     */
    public function index($flightId)
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('Review index error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }
}
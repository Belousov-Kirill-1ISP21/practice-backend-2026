<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * POST /bookings - Создать новое бронирование
     */
    public function store(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            abort(400, 'Invalid JSON format');
        }
        
        $validator = \Validator::make($data, [
            'flight_id' => 'required|exists:flights,id',
            'passengers' => 'required|array|min:1|max:9',
            'passengers.*.first_name' => 'required|string|max:100',
            'passengers.*.last_name' => 'required|string|max:100',
            'passengers.*.middle_name' => 'nullable|string|max:100',
            'passengers.*.birth_date' => 'nullable|date',
            'passengers.*.passport_number' => 'nullable|string|max:20',
            'passengers.*.seat_number' => 'nullable|string|max:5'
        ]);

        if ($validator->fails()) {
            abort(422, $validator->errors());
        }

        $validated = $validator->validated();
        
        $flight = Flight::with('aircraft')->find($validated['flight_id']);
        
        if (!$flight) {
            abort(404, 'Рейс с указанным ID не найден');
        }
        
        if ($flight->status !== 'scheduled') {
            abort(409, 'Рейс недоступен для бронирования');
        }

        $availableSeats = $this->getAvailableSeatsCount($flight);

        $existingBooking = Booking::where('user_id', auth()->id())
            ->where('flight_id', $validated['flight_id'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            abort(409, 'У вас уже есть бронирование на этот рейс');
        }
        
        if ($availableSeats <= 0) {
            abort(409, 'На рейсе нет свободных мест');
        }
        
        if (count($validated['passengers']) > $availableSeats) {
            abort(409, "Недостаточно мест. Доступно: {$availableSeats}");
        }

        if ($this->areSeatsTaken($flight->id, $validated['passengers'])) {
            abort(409, 'Некоторые выбранные места уже заняты');
        }

        $booking = Booking::create([
            'user_id' => auth()->id(),
            'flight_id' => $flight->id,
            'code' => $this->generateBookingCode(),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(30),
            'total_price' => $flight->base_price * count($validated['passengers']),
            'payment_status' => 'pending'
        ]);

        foreach ($validated['passengers'] as $passengerData) {
            $passengerData['ticket_price'] = $flight->base_price;
            $booking->passengers()->create($passengerData);
        }

        Log::info('Booking created', [
            'user_id' => auth()->id(),
            'booking_id' => $booking->id,
            'code' => $booking->code
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => $booking->load('passengers'),
                'expires_at' => $booking->expires_at,
                'minutes_to_pay' => 30
            ],
            'message' => 'Бронирование создано. Оплатите в течение 30 минут.'
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /bookings/{id} - Получить детали бронирования
     */
    public function show($id)
    {
        $booking = Booking::with(['flight.origin', 'flight.destination', 'flight.aircraft', 'passengers'])
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /bookings/{id}/cancel - Отменить бронирование (пользователь)
     */
    public function cancel($id)
    {
        $booking = Booking::where('user_id', auth()->id())->find($id);

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            abort(409, 'Нельзя отменить бронирование в статусе ' . $booking->status);
        }

        if ($booking->status === 'confirmed') {
            $flight = $booking->flight;
            $hoursToDeparture = Carbon::parse($flight->departure_time)->diffInHours(now());
            
            if ($hoursToDeparture < 24) {
                abort(409, 'Отмена возможна не менее чем за 24 часа до вылета');
            }
        }

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => $booking->payment_status === 'paid' ? 'refunded' : 'pending'
        ]);

        Log::info('Booking cancelled by user', [
            'user_id' => auth()->id(),
            'booking_id' => $booking->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Бронирование успешно отменено'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /admin/bookings/{id}/cancel - Отменить бронирование (админ)
     */
    public function adminCancel($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => $booking->payment_status === 'paid' ? 'refunded' : 'pending'
        ]);

        Log::info('Booking cancelled by admin', [
            'admin_id' => auth()->id(),
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Бронирование успешно отменено администратором'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /bookings/{id}/pay - Оплатить бронирование
     */
    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:card,cash,online',
            'card_number' => 'required_if:payment_method,card|string|size:16'
        ]);

        $booking = Booking::where('user_id', auth()->id())->find($id);

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        if ($booking->status !== 'pending') {
            abort(409, 'Можно оплатить только бронирования в статусе pending');
        }

        if (Carbon::parse($booking->expires_at)->isPast()) {
            $booking->update(['status' => 'expired']);
            abort(409, 'Время оплаты истекло');
        }

        $paymentSuccessful = true;

        if ($paymentSuccessful) {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            Log::info('Booking paid', [
                'user_id' => auth()->id(),
                'booking_id' => $booking->id,
                'payment_method' => $validated['payment_method']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Оплата прошла успешно',
                'data' => [
                    'booking_code' => $booking->code,
                    'total_price' => $booking->total_price,
                    'status' => 'confirmed'
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            abort(402, 'Ошибка при оплате');
        }
    }

    /**
     * GET /bookings/my - Получить свои бронирования
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with(['flight.origin', 'flight.destination', 'passengers'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /admin/bookings - Получить все бронирования (только админ)
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'flight.origin', 'flight.destination', 'passengers']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to));
        }

        if ($request->has('flight_id')) {
            $query->where('flight_id', $request->flight_id);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /admin/bookings/{id} - Получить детали бронирования (только админ)
     */
    public function adminShow($id)
    {
        $booking = Booking::with(['user', 'flight.origin', 'flight.destination', 'flight.aircraft', 'passengers'])
            ->find($id);

        if (!$booking) {
            abort(404, 'Бронирование не найдено');
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Вспомогательные методы
     */
    private function getAvailableSeatsCount($flight)
    {
        $bookedSeats = $flight->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('passengers')
            ->get()
            ->sum(function($booking) {
                return $booking->passengers->count();
            });
        
        return $flight->aircraft->total_seats - $bookedSeats;
    }

    private function areSeatsTaken($flightId, $passengers)
    {
        $seatNumbers = array_column($passengers, 'seat_number');
        $seatNumbers = array_filter($seatNumbers); 
        
        if (empty($seatNumbers)) {
            return false;
        }

        $takenSeats = Passenger::whereHas('booking', function($q) use ($flightId) {
                $q->where('flight_id', $flightId)
                  ->whereIn('status', ['pending', 'confirmed']);
            })
            ->whereIn('seat_number', $seatNumbers)
            ->exists();

        return $takenSeats;
    }

    private function generateBookingCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Booking::where('code', $code)->exists());

        return $code;
    }
}
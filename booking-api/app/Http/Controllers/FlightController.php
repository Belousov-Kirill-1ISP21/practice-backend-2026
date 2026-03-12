<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FlightController extends Controller
{
    /**
     * GET /flights - Поиск рейсов с фильтрацией
     */
    public function index(Request $request)
    {
        try {
            $query = Flight::with(['origin', 'destination', 'aircraft']);
            
            if ($request->has('departure_city')) {
                $query->whereHas('origin', function($q) use ($request) {
                    $q->where('city', 'LIKE', '%' . $request->departure_city . '%');
                });
            }
            
            if ($request->has('arrival_city')) {
                $query->whereHas('destination', function($q) use ($request) {
                    $q->where('city', 'LIKE', '%' . $request->arrival_city . '%');
                });
            }
            
            if ($request->has('date')) {
                $date = Carbon::parse($request->date);
                $query->whereDate('departure_time', $date);
            }
            
            if ($request->has('min_price')) {
                $query->where('base_price', '>=', $request->min_price);
            }
            
            if ($request->has('max_price')) {
                $query->where('base_price', '<=', $request->max_price);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $sortBy = $request->get('sort_by', 'departure_time');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
            
            $flights = $query->get();
            
            foreach ($flights as $flight) {
                $flight->available_seats = $this->getAvailableSeatsCount($flight);
            }
            
            return response()->json([
                'success' => true,
                'data' => $flights
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Flight index error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /flights/{id} - Получить детали рейса
     */
    public function show($id)
    {
        try {
            $flight = Flight::with(['origin', 'destination', 'aircraft'])->find($id);
            
            if (!$flight) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Рейс не найден'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
            
            $flight->available_seats = $this->getAvailableSeatsCount($flight);
            
            return response()->json([
                'success' => true,
                'data' => $flight
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Flight show error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * POST /flights - Создать новый рейс (только админ)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'flight_number' => 'required|string|max:10|unique:flights',
                'origin_airport_id' => 'required|exists:airports,id',
                'dest_airport_id' => 'required|exists:airports,id|different:origin_airport_id',
                'aircraft_id' => 'required|exists:aircrafts,id',
                'departure_time' => 'required|date|after:now',
                'arrival_time' => 'required|date|after:departure_time',
                'base_price' => 'required|numeric|min:0',
                'status' => 'sometimes|in:scheduled,boarding,departed,arrived,cancelled'
            ]);
            
            $flight = Flight::create($validated);
            
            Log::info('Flight created', ['user_id' => auth()->id(), 'flight_id' => $flight->id]);
            
            return response()->json([
                'success' => true,
                'data' => $flight,
                'message' => 'Рейс успешно создан'
            ], 201, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => $e->errors()
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Flight store error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * PUT /flights/{id} - Обновить данные рейса (только админ)
     */
    public function update(Request $request, $id)
    {
        try {
            $flight = Flight::find($id);
            
            if (!$flight) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Рейс не найден'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
            
            $validated = $request->validate([
                'flight_number' => 'sometimes|string|max:10|unique:flights,flight_number,' . $id,
                'origin_airport_id' => 'sometimes|exists:airports,id',
                'dest_airport_id' => 'sometimes|exists:airports,id|different:origin_airport_id',
                'aircraft_id' => 'sometimes|exists:aircrafts,id',
                'departure_time' => 'sometimes|date',
                'arrival_time' => 'sometimes|date|after:departure_time',
                'base_price' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:scheduled,boarding,departed,arrived,cancelled'
            ]);
            
            if ($flight->bookings()->exists() && isset($validated['status']) && $validated['status'] === 'cancelled') {
                return response()->json([
                    'error' => 'HAS_BOOKINGS',
                    'message' => 'Нельзя отменить рейс с активными бронированиями'
                ], 409, [], JSON_UNESCAPED_UNICODE);
            }
            
            $flight->update($validated);
            
            Log::info('Flight updated', ['user_id' => auth()->id(), 'flight_id' => $flight->id]);
            
            return response()->json([
                'success' => true,
                'data' => $flight,
                'message' => 'Рейс успешно обновлен'
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => $e->errors()
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Flight update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * DELETE /flights/{id} - Удалить рейс (только админ)
     */
    public function destroy($id)
    {
        try {
            $flight = Flight::find($id);
            
            if (!$flight) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Рейс не найден'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
            
            if ($flight->bookings()->exists()) {
                return response()->json([
                    'error' => 'HAS_BOOKINGS',
                    'message' => 'Нельзя удалить рейс с существующими бронированиями'
                ], 409, [], JSON_UNESCAPED_UNICODE);
            }
            
            $flight->delete();
            
            Log::info('Flight deleted', ['user_id' => auth()->id(), 'flight_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Рейс успешно удален'
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Flight destroy error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Вспомогательный метод для подсчета свободных мест
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
}
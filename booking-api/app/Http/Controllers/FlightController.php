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
        
        $perPage = $request->get('per_page', 15);
        $flights = $query->paginate($perPage);
        
        foreach ($flights as $flight) {
            $flight->available_seats = $this->getAvailableSeatsCount($flight);
            $flight->average_rating = $flight->reviews()->avg('rating') ?: 0;
            $flight->reviews_count = $flight->reviews()->count();
        }
        
        return response()->json([
            'success' => true,
            'data' => $flights
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /flights/{id} - Получить детали рейса
     */
    public function show($id)
    {
        $flight = Flight::with(['origin', 'destination', 'aircraft'])->find($id);
        
        if (!$flight) {
            abort(404, 'Рейс не найден');
        }
        
        $flight->available_seats = $this->getAvailableSeatsCount($flight);
        $flight->average_rating = $flight->reviews()->avg('rating') ?: 0;
        $flight->reviews_count = $flight->reviews()->count();
        
        return response()->json([
            'success' => true,
            'data' => $flight
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /flights - Создать новый рейс (только админ)
     */
    public function store(Request $request)
    {
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
    }

    /**
     * PUT /flights/{id} - Обновить данные рейса (только админ)
     */
    public function update(Request $request, $id)
    {
        $flight = Flight::find($id);
        
        if (!$flight) {
            abort(404, 'Рейс не найден');
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
            abort(409, 'Нельзя отменить рейс с активными бронированиями');
        }
        
        $flight->update($validated);
        
        Log::info('Flight updated', ['user_id' => auth()->id(), 'flight_id' => $flight->id]);
        
        return response()->json([
            'success' => true,
            'data' => $flight,
            'message' => 'Рейс успешно обновлен'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * DELETE /flights/{id} - Удалить рейс (только админ)
     */
    public function destroy($id)
    {
        $flight = Flight::find($id);
        
        if (!$flight) {
            abort(404, 'Рейс не найден');
        }
        
        if ($flight->bookings()->exists()) {
            abort(409, 'Нельзя удалить рейс с существующими бронированиями');
        }
        
        $flight->delete();
        
        Log::info('Flight deleted', ['user_id' => auth()->id(), 'flight_id' => $id]);
        
        return response()->json([
            'success' => true,
            'message' => 'Рейс успешно удален'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /flights/available - Поиск свободных рейсов
     */
    public function available(Request $request)
    {
        $query = Flight::with(['origin', 'destination', 'aircraft'])
            ->where('status', 'scheduled');
        
        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        }
        
        if ($request->has('from_date') && $request->has('to_date')) {
            $from = Carbon::parse($request->from_date)->startOfDay();
            $to = Carbon::parse($request->to_date)->endOfDay();
            $query->whereBetween('departure_time', [$from, $to]);
        }
        
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
        
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }
        
        $flights = $query->get();

        Log::info('Available flights query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => $flights->count(),
            'date' => $request->date
        ]);

        $result = [];
        foreach ($flights as $flight) {
            $available = $this->getAvailableSeatsCount($flight);
            
            if ($request->has('min_available_seats') && $available < $request->min_available_seats) {
                continue;
            }
            
            $flight->available_seats = $available;
            $flight->average_rating = $flight->reviews()->avg('rating') ?: 0;
            $result[] = $flight;
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * GET /flights/schedule - Расписание рейсов на день/неделю
     */
    public function schedule(Request $request)
    {
        $query = Flight::with(['origin', 'destination', 'aircraft'])
            ->where('status', 'scheduled');
        
        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('departure_time', $date);
        }
        
        if ($request->has('week')) {
            $startOfWeek = Carbon::parse($request->week)->startOfWeek();
            $endOfWeek = Carbon::parse($request->week)->endOfWeek();
            $query->whereBetween('departure_time', [$startOfWeek, $endOfWeek]);
        }
        
        $flights = $query->orderBy('departure_time')->get();
        
        foreach ($flights as $flight) {
            $flight->available_seats = $this->getAvailableSeatsCount($flight);
            $flight->average_rating = $flight->reviews()->avg('rating') ?: 0;
        }
        
        return response()->json([
            'success' => true,
            'data' => $flights
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
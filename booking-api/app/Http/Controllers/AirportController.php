<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AirportController extends Controller
{
    /**
    * GET /airports - Получить список всех аэропортов
    */
    public function index(Request $request)
    {
        $query = Airport::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('city', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }
        
        $perPage = $request->get('per_page', 15);
        $airports = $query->orderBy('city')->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $airports
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /airports - Создать новый аэропорт (только админ)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:airports',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'timezone' => 'required|string|max:50'
        ]);
        
        $airport = Airport::create($validated);
        
        return response()->json([
            'success' => true,
            'data' => $airport,
            'message' => 'Аэропорт успешно создан'
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /airports/{id} - Обновить аэропорт (только админ)
     */
    public function update(Request $request, $id)
    {
        $airport = Airport::find($id);
        
        if (!$airport) {
            abort(404, 'Аэропорт не найден');
        }
        
        $validated = $request->validate([
            'code' => 'sometimes|string|size:3|unique:airports,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'timezone' => 'sometimes|string|max:50'
        ]);
        
        $airport->update($validated);
        
        Log::info('Airport updated', ['user_id' => auth()->id(), 'airport_id' => $airport->id]);
        
        return response()->json([
            'success' => true,
            'data' => $airport,
            'message' => 'Аэропорт успешно обновлен'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * DELETE /airports/{id} - Удалить аэропорт (только админ)
     */
    public function destroy($id)
    {
        $airport = Airport::find($id);
        
        if (!$airport) {
            abort(404, 'Аэропорт не найден');
        }
        
        if ($airport->originFlights()->exists() || $airport->destinationFlights()->exists()) {
            abort(409, 'Нельзя удалить аэропорт, с которым связаны рейсы');
        }
        
        $airport->delete();
        
        Log::info('Airport deleted', ['user_id' => auth()->id(), 'airport_id' => $id]);
        
        return response()->json([
            'success' => true,
            'message' => 'Аэропорт успешно удален'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
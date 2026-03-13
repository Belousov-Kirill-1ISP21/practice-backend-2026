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
        try {
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
            
        } catch (\Exception $e) {
            Log::error('Airport index error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
}

    /**
     * POST /airports - Создать новый аэропорт (только админ)
     */
    public function store(Request $request)
{

    try {
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
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'error' => 'VALIDATION_ERROR',
            'message' => $e->errors()
        ], 422, [], JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'SERVER_ERROR',
            'message' => 'Внутренняя ошибка сервера'
        ], 500, [], JSON_UNESCAPED_UNICODE);
    }
}

    /**
     * PUT /airports/{id} - Обновить аэропорт (только админ)
     */
    public function update(Request $request, $id)
    {
        try {
            $airport = Airport::find($id);
            
            if (!$airport) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Аэропорт не найден'
                ], 404, [], JSON_UNESCAPED_UNICODE);
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => $e->errors()
            ], 422, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Airport update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * DELETE /airports/{id} - Удалить аэропорт (только админ)
     */
    public function destroy($id)
    {
        try {
            $airport = Airport::find($id);
            
            if (!$airport) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Аэропорт не найден'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }
            
            if ($airport->originFlights()->exists() || $airport->destinationFlights()->exists()) {
                return response()->json([
                    'error' => 'HAS_RELATED_FLIGHTS',
                    'message' => 'Нельзя удалить аэропорт, с которым связаны рейсы'
                ], 409, [], JSON_UNESCAPED_UNICODE);
            }
            
            $airport->delete();
            
            Log::info('Airport deleted', ['user_id' => auth()->id(), 'airport_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Аэропорт успешно удален'
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Airport destroy error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
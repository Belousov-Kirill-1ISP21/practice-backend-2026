<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AircraftController extends Controller
{
    /**
     * GET /aircrafts - Получить список всех самолетов
     */
    public function index(Request $request)
    {
        $query = Aircraft::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->has('min_seats')) {
            $query->where('total_seats', '>=', $request->min_seats);
        }
        
        if ($request->has('max_seats')) {
            $query->where('total_seats', '<=', $request->max_seats);
        }
        
        $perPage = $request->get('per_page', 15);
        $aircrafts = $query->withCount('flights')
            ->orderBy('model')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $aircrafts
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /aircrafts - Создать новый самолет (только админ)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100|unique:aircrafts',
            'manufacturer' => 'required|string|max:100',
            'total_seats' => 'required|integer|min:1|max:1000'
        ]);
        
        $aircraft = Aircraft::create($validated);
        
        Log::info('Aircraft created', ['user_id' => auth()->id(), 'aircraft_id' => $aircraft->id]);
        
        return response()->json([
            'success' => true,
            'data' => $aircraft,
            'message' => 'Самолет успешно создан'
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * PUT /aircrafts/{id} - Обновить самолет (только админ)
     */
    public function update(Request $request, $id)
    {
        $aircraft = Aircraft::find($id);
        
        if (!$aircraft) {
            abort(404, 'Самолет не найден');
        }
        
        $validated = $request->validate([
            'model' => 'sometimes|string|max:100|unique:aircrafts,model,' . $id,
            'manufacturer' => 'sometimes|string|max:100',
            'total_seats' => 'sometimes|integer|min:1|max:1000'
        ]);
        
        $aircraft->update($validated);
        
        Log::info('Aircraft updated', ['user_id' => auth()->id(), 'aircraft_id' => $aircraft->id]);
        
        return response()->json([
            'success' => true,
            'data' => $aircraft,
            'message' => 'Самолет успешно обновлен'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * DELETE /aircrafts/{id} - Удалить самолет (только админ)
     */
    public function destroy($id)
    {
        $aircraft = Aircraft::find($id);
        
        if (!$aircraft) {
            abort(404, 'Самолет не найден');
        }
        
        if ($aircraft->flights()->exists()) {
            abort(409, 'Нельзя удалить самолет, который используется в рейсах');
        }
        
        $aircraft->delete();
        
        Log::info('Aircraft deleted', ['user_id' => auth()->id(), 'aircraft_id' => $id]);
        
        return response()->json([
            'success' => true,
            'message' => 'Самолет успешно удален'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;

Route::post('test', function(\Illuminate\Http\Request $request) {
    return response()->json([
        'all' => $request->all(),
        'email' => $request->email,
        'json' => json_decode(file_get_contents('php://input'), true)
    ]);
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    
    Route::middleware('admin')->group(function () {

    });
});
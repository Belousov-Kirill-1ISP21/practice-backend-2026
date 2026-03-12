<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\AircraftController;


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    
    Route::get('flights', [FlightController::class, 'index']);
    Route::get('flights/{id}', [FlightController::class, 'show']);
    
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/my', [BookingController::class, 'myBookings']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::post('bookings/{id}/pay', [BookingController::class, 'pay']);
    
    Route::get('airports', [AirportController::class, 'index']);
    
    Route::get('aircrafts', [AircraftController::class, 'index']);
    
    Route::middleware('admin')->group(function () {

        Route::post('flights', [FlightController::class, 'store']);
        Route::put('flights/{id}', [FlightController::class, 'update']);
        Route::delete('flights/{id}', [FlightController::class, 'destroy']);
        
        Route::post('airports', [AirportController::class, 'store']);
        Route::put('airports/{id}', [AirportController::class, 'update']);
        Route::delete('airports/{id}', [AirportController::class, 'destroy']);
        
        Route::post('aircrafts', [AircraftController::class, 'store']);
        Route::put('aircrafts/{id}', [AircraftController::class, 'update']);
        Route::delete('aircrafts/{id}', [AircraftController::class, 'destroy']);
        
        Route::get('admin/bookings', [BookingController::class, 'index']);
        Route::get('admin/bookings/{id}', [BookingController::class, 'adminShow']);
    });
});
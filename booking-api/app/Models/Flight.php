<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public $timestamps = false;  
    
    protected $table = 'flights';
    protected $fillable = [
        'flight_number', 'origin_airport_id', 'dest_airport_id',
        'aircraft_id', 'departure_time', 'arrival_time', 'base_price', 'status'
    ];
    
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime'
    ];
    
    public function origin()
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }
    
    public function destination()
    {
        return $this->belongsTo(Airport::class, 'dest_airport_id');
    }
    
    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
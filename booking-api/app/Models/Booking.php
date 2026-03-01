<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public $timestamps = false; 
    
    protected $table = 'bookings';
    protected $fillable = [
        'user_id', 'flight_id', 'code', 'status',
        'expires_at', 'total_price', 'payment_status'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }
    
    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }
}
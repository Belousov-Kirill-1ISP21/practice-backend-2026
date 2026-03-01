<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    public $timestamps = false; 
    
    protected $table = 'passengers';
    protected $fillable = [
        'booking_id', 'last_name', 'first_name', 'middle_name',
        'birth_date', 'passport_number', 'seat_number', 'ticket_price'
    ];
    
    protected $casts = [
        'birth_date' => 'date'
    ];
    
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
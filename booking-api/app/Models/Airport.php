<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    public $timestamps = false;  
    
    protected $table = 'airports';
    protected $fillable = ['code', 'name', 'city', 'country', 'timezone'];
    
    public function originFlights()
    {
        return $this->hasMany(Flight::class, 'origin_airport_id');
    }
    
    public function destinationFlights()
    {
        return $this->hasMany(Flight::class, 'dest_airport_id');
    }
}
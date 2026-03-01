<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    public $timestamps = false;  
    
    protected $table = 'aircrafts';
    protected $fillable = ['model', 'manufacturer', 'total_seats'];
    
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
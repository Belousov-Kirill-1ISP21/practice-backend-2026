<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    public $timestamps = false;
    
    protected $table = 'users';
    protected $fillable = [
        'email', 'password_hash', 'role_id', 
        'last_name', 'first_name', 'middle_name',
        'phone', 'passport_number', 'birth_date'
    ];
    protected $hidden = ['password_hash'];
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role->name
        ];
    }
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
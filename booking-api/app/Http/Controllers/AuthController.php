<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $userRole = Role::where('name', 'user')->first();
        
        $user = User::create([
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role_id' => $userRole->id,
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'phone' => $request->phone,
            'passport_number' => $request->passport_number,
            'birth_date' => $request->birth_date
        ]);
        
        $token = JWTAuth::fromUser($user);
        
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->last_name . ' ' . $user->first_name,
                'role' => $user->role->name
            ]
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Неверный email или пароль'
                ]
            ], 401);
        }
        
        $token = JWTAuth::fromUser($user);
        
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->last_name . ' ' . $user->first_name,
                'role' => $user->role->name
            ]
        ]);
    }

    public function me()
    {
        $user = auth()->user();
        $user->load('role');
        
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'last_name' => $user->last_name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'phone' => $user->phone,
            'passport_number' => $user->passport_number,
            'birth_date' => $user->birth_date,
            'role' => $user->role->name
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Успешный выход']);
    }
}
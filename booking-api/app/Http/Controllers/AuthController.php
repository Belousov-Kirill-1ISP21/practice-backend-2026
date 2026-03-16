<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            abort(400, 'Invalid JSON format');
        }
        
        $validator = \Validator::make($data, [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:255',
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            abort(422, $validator->errors());
        }

        $validated = $validator->validated();
        
        if (User::where('email', $validated['email'])->exists()) {
            abort(409, 'Пользователь с таким email уже существует');
        }
        
        $userRole = Role::where('name', 'user')->first();
        if (!$userRole) {
            abort(500, 'Роль user не найдена в базе данных');
        }
        
        $user = User::create([
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role_id' => $userRole->id,
            'last_name' => $validated['last_name'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'passport_number' => $validated['passport_number'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null
        ]);
        
        $token = JWTAuth::fromUser($user);
        
        Log::info('User registered', ['user_id' => $user->id, 'email' => $user->email]);
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->last_name . ' ' . $user->first_name,
                'role' => $user->role->name
            ]
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            abort(401, 'Неверный email или пароль');
        }
        
        $token = JWTAuth::fromUser($user);
        
        Log::info('User logged in', ['user_id' => $user->id, 'email' => $user->email]);
        
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->last_name . ' ' . $user->first_name,
                'role' => $user->role->name
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function me()
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Не авторизован');
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'phone' => $user->phone,
                'role' => $user->role->name
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function logout()
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Не авторизован');
        }
        
        auth()->logout();
        
        Log::info('User logged out', ['user_id' => $user->id, 'email' => $user->email]);
        
        return response()->json([
            'success' => true,
            'message' => 'Успешный выход'
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
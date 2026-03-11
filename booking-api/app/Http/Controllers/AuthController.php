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
        try {
            // Получаем данные
            $content = $request->getContent();
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            $data = json_decode($content, true);
            
            if (!$data) {
                $data = $request->all();
            }
            
            // Валидация обязательных полей
            if (!isset($data['email']) || !isset($data['password']) || 
                !isset($data['last_name']) || !isset($data['first_name'])) {
                return response()->json([
                    'error' => 'VALIDATION_ERROR',
                    'message' => 'Обязательные поля: email, password, last_name, first_name'
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Проверяем, существует ли пользователь
            if (User::where('email', $data['email'])->exists()) {
                return response()->json([
                    'error' => 'EMAIL_EXISTS',
                    'message' => 'Пользователь с таким email уже существует'
                ], 409, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Получаем роль user
            $userRole = Role::where('name', 'user')->first();
            if (!$userRole) {
                return response()->json([
                    'error' => 'SERVER_ERROR',
                    'message' => 'Роль user не найдена в базе данных'
                ], 500, [], JSON_UNESCAPED_UNICODE);
            }
            
            // Создаем пользователя
            $user = User::create([
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'role_id' => $userRole->id,
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'passport_number' => $data['passport_number'] ?? null,
                'birth_date' => $data['birth_date'] ?? null
            ]);
            
            // Создаем токен
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
            
        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                return response()->json([
                    'error' => [
                        'code' => 'INVALID_CREDENTIALS',
                        'message' => 'Неверный email или пароль'
                    ]
                ], 401, [], JSON_UNESCAPED_UNICODE);
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
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function me()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->role_id
        ]);
    }

    public function logout()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'error' => 'UNAUTHORIZED',
                    'message' => 'Не авторизован'
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }
            
            auth()->logout();
            
            Log::info('User logged out', ['user_id' => $user->id, 'email' => $user->email]);
            
            return response()->json([
                'success' => true,
                'message' => 'Успешный выход'
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'error' => 'SERVER_ERROR',
                'message' => 'Внутренняя ошибка сервера'
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
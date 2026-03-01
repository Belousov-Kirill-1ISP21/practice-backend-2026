<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|unique:users|max:20',
            'birth_date' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Email обязателен',
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Email уже занят',
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Пароль должен быть минимум 8 символов',
            'last_name.required' => 'Фамилия обязательна',
            'first_name.required' => 'Имя обязательно'
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); 
    }

    public function rules()
    {
        return [
            'flight_id' => 'required|exists:flights,id',
            'passengers' => 'required|integer|min:1|max:9'
        ];
    }

    public function messages()
    {
        return [
            'flight_id.required' => 'Выберите рейс',
            'flight_id.exists' => 'Рейс не найден',
            'passengers.required' => 'Укажите количество пассажиров',
            'passengers.min' => 'Минимум 1 пассажир',
            'passengers.max' => 'Максимум 9 пассажиров'
        ];
    }
}
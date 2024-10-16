<?php

namespace App\Http\Requests\Tour;

use Illuminate\Foundation\Http\FormRequest;

class CreateTourRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'guide_id' => 'required|exists:guides,id',
            'driver_id' => 'required|exists:drivers,id',
            'program_id' => 'required|exists:programs,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ];
    }

    public function messages()
    {
        return [
            'guide_id.required' => 'The guide is required.',
            'guide_id.exists' => 'The selected guide does not exist.',
            'driver_id.required' => 'The driver is required.',
            'driver_id.exists' => 'The selected driver does not exist.',
            'program_id.required' => 'The program is required.',
            'program_id.exists' => 'The selected program does not exist.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.after_or_equal' => 'The start date must be today or later.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after' => 'The end date must be after the start date.',
        ];
    }
}

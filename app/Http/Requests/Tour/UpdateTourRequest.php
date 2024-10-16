<?php

namespace App\Http\Requests\Tour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTourRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all users (you can modify this to check user roles)
    }

    public function rules()
    {
        return [
            'guide_id' => 'sometimes|exists:guides,id',
            'driver_id' => 'sometimes|exists:drivers,id',
            'price' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date|after:now',
            'end_date' => 'sometimes|date|after:start_date',
            'description' => 'sometimes|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'guide_id.sometimes' => 'The guide field is required only if provided.',
            'guide_id.exists' => 'The selected guide does not exist.',
            'driver_id.sometimes' => 'The driver field is required only if provided.',
            'driver_id.exists' => 'The selected driver does not exist.',
            'start_date.sometimes' => 'The start date is required only if provided.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.after' => 'The start date must be a date after today.',
            'end_date.sometimes' => 'The end date is required only if provided.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after' => 'The end date must be after the start date.',
            'description.sometimes' => 'The description is required only if provided.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
        ];
    }
}

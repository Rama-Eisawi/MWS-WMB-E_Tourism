<?php

namespace App\Http\Requests\Program;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all users to attempt to update a program
    }

    public function rules()
    {
        return [
            'type' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'The type field is required.',
            'name.required' => 'The name field is required.',
        ];
    }
}

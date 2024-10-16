<?php

namespace App\Http\Requests\Program;

use Illuminate\Foundation\Http\FormRequest;

class CreateProgramRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
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

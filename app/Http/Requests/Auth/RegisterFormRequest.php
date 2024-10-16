<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8', // Minimum length of 8 characters
                'regex:/[a-z]/',     // At least one lowercase letter
                'regex:/[A-Z]/',     // At least one uppercase letter
                'regex:/[0-9]/',     // At least one digit
                'regex:/[@$!%*?&-_]/', // At least one special character
            ],
            'fName' => ['required', 'string', 'max:255'],
            'lName' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
    // Define custom attribute names for validation messages
    public function attributes()
    {
        return [
            'fName' => 'first name',
            'lName' => 'last name',
        ];
    }
    // Optional: Define custom messages for validation errors
    public function messages()
    {
        return [
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already taken.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            //'role.in' => 'The role must be one of the following: tourist, driver, guide, admin.',
        ];
    }
}

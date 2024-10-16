<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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

        $rules = [
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
            'role' => ['required', 'in:tourist,driver,guide,admin']
        ];

        // Conditional validation for drivers (requires plate_number)
        if ($this->input('role') === 'driver') {
            $rules['plate_number'] = 'required|string|unique:drivers,plate_number';
        }

        // Conditional validation for guides (requires address and mobile)
        if ($this->input('role') === 'guide') {
            $rules['address'] = 'required|string|max:255';
            $rules['mobile'] = 'required|string|unique:guides,mobile';
        }

        return $rules;
    }
    // Define custom attribute names for validation messages
    public function attributes()
    {
        return [
            'fName' => 'first name',
            'lName' => 'last name',
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already taken.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.in' => 'The role must be one of the following: tourist, driver, guide, admin.',
            'plate_number.required' => 'The plate number is required for drivers.',
            'address.required' => 'The address is required for guides.',
            'mobile.required' => 'The mobile number is required for guides.',
        ];
    }
}

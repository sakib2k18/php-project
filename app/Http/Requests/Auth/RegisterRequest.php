<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * There is deliberately no `role` rule. The controller builds the User with
     * explicit fields only, so submitting role=admin has no effect whatsoever.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'student_id' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An account with that email address already exists.',
            'terms.accepted' => 'Please accept the terms to create an account.',
            'phone.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
        ];
    }
}

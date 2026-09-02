<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesImages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        // A user may only ever edit their own profile (UserPolicy::update).
        return $this->user()?->can('update', $this->user()) ?? false;
    }

    /**
     * `role` and `is_active` are absent by design: privileges are not
     * self-service.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => [
                'required', 'email:rfc', 'max:150',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'student_id' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('avatar') + [
            'email.unique' => 'That email address is already registered.',
            'phone.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
        ];
    }
}

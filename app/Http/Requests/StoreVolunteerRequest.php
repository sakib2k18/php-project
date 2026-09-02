<?php

namespace App\Http\Requests;

use App\Models\Volunteer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVolunteerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Volunteer::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'student_id' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-\/]+$/'],
            'institution' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'skills' => ['required', 'string', 'min:5', 'max:500'],
            'availability' => ['required', Rule::in(array_keys(config('site.volunteer_availability')))],
            'preferred_activity' => ['required', 'string', 'max:60'],
            'motivation' => ['required', 'string', 'min:30', 'max:1500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivation.min' => 'Tell us a little more — at least 30 characters.',
            'phone.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
            'student_id.regex' => 'The student/organisation ID may only contain letters, numbers, - and /.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationSettingsRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'org_name' => ['required', 'string', 'min:2', 'max:120'],
            'tagline' => ['required', 'string', 'max:180'],
            'about' => ['required', 'string', 'min:50', 'max:4000'],
            'mission' => ['required', 'string', 'min:20', 'max:2000'],
            'vision' => ['required', 'string', 'min:20', 'max:2000'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'emergency_contact' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'address' => ['required', 'string', 'max:255'],
            'office_hours' => ['nullable', 'string', 'max:150'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'bank_details' => ['nullable', 'string', 'max:1000'],
            'mobile_banking_details' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'logo' => $this->imageRules(),
            'favicon' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('logo') + $this->imageMessages('favicon') + [
            'phone.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
            'emergency_contact.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
        ];
    }
}

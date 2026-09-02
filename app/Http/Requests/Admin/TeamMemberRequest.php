<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\TeamMember;
use Illuminate\Foundation\Http\FormRequest;

class TeamMemberRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        $member = $this->route('team_member');

        return $member
            ? ($this->user()?->can('update', $member) ?? false)
            : ($this->user()?->can('create', TeamMember::class) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'position' => ['required', 'string', 'min:2', 'max:120'],
            'biography' => ['nullable', 'string', 'max:1500'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('photo');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order') ?: 0,
        ]);
    }
}

<?php

namespace App\Http\Requests\Admin;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement
            ? ($this->user()?->can('update', $announcement) ?? false)
            : ($this->user()?->can('create', Announcement::class) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
            'priority' => ['required', Rule::in(array_keys(config('site.announcement_priorities')))],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'link_url' => ['nullable', 'url', 'max:255'],
            'link_label' => ['nullable', 'string', 'max:60', 'required_with:link_url'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:published_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'expires_at.after' => 'The expiry date must be after the publish date.',
            'link_label.required_with' => 'Give the link a short button label.',
        ];
    }
}

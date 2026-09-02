<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateSuccessStoryRequest extends StoreSuccessStoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('story')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'nullable', 'string', 'max:200', 'alpha_dash',
            Rule::unique('success_stories', 'slug')->ignore($this->route('story')?->id)->withoutTrashed(),
        ];

        return $rules;
    }
}

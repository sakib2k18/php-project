<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdatePostRequest extends StorePostRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('post')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'nullable', 'string', 'max:200', 'alpha_dash',
            Rule::unique('posts', 'slug')->ignore($this->route('post')?->id)->withoutTrashed(),
        ];

        return $rules;
    }
}

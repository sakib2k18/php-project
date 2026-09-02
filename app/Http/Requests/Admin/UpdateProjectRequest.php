<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateProjectRequest extends StoreProjectRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('project')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'nullable', 'string', 'max:200', 'alpha_dash',
            Rule::unique('projects', 'slug')->ignore($this->route('project')?->id)->withoutTrashed(),
        ];

        return $rules;
    }
}

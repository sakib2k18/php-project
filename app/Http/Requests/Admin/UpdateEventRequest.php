<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateEventRequest extends StoreEventRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'nullable', 'string', 'max:200', 'alpha_dash',
            Rule::unique('events', 'slug')->ignore($this->route('event')?->id)->withoutTrashed(),
        ];

        return $rules;
    }
}

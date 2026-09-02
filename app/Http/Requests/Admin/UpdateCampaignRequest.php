<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends StoreCampaignRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('campaign')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['slug'] = [
            'nullable', 'string', 'max:200', 'alpha_dash',
            Rule::unique('campaigns', 'slug')
                ->ignore($this->route('campaign')?->id)
                ->withoutTrashed(),
        ];

        return $rules;
    }
}

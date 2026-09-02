<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Campaign::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('campaigns', 'slug')->withoutTrashed(),
            ],
            'short_description' => ['required', 'string', 'min:20', 'max:500'],
            'description' => ['required', 'string', 'min:50'],
            'category' => ['required', Rule::in(array_keys(config('site.campaign_categories')))],
            'target_amount' => ['required', 'numeric', 'min:100', 'max:999999999'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'status' => ['required', Rule::in(array_keys(config('site.campaign_statuses')))],
            'featured' => ['nullable', 'boolean'],
            'is_emergency' => ['nullable', 'boolean'],
            'beneficiaries_count' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'cover_image' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('cover_image') + [
            'target_amount.min' => 'A campaign target should be at least '.money(100).'.',
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
            'latitude.required_with' => 'Provide both latitude and longitude, or neither.',
            'longitude.required_with' => 'Provide both latitude and longitude, or neither.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'featured' => $this->boolean('featured'),
            'is_emergency' => $this->boolean('is_emergency'),
            'beneficiaries_count' => $this->input('beneficiaries_count') ?: 0,
            'slug' => $this->filled('slug') ? str($this->input('slug'))->slug()->toString() : null,
        ]);
    }
}

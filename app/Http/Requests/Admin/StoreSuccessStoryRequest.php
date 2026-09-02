<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\SuccessStory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuccessStoryRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        return $this->user()?->can('create', SuccessStory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', Rule::unique('success_stories', 'slug')->withoutTrashed()],
            'beneficiary_name' => ['nullable', 'string', 'max:120'],
            'beneficiary_description' => ['nullable', 'string', 'max:500'],
            'story' => ['required', 'string', 'min:80'],
            'location' => ['nullable', 'string', 'max:180'],
            'story_date' => ['required', 'date', 'before_or_equal:today'],
            'campaign_id' => ['nullable', 'integer', Rule::exists('campaigns', 'id')->whereNull('deleted_at')],
            'is_published' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'image' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages() + [
            'story_date.before_or_equal' => 'A success story cannot be dated in the future.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'featured' => $this->boolean('featured'),
            'campaign_id' => $this->input('campaign_id') ?: null,
            'slug' => $this->filled('slug') ? str($this->input('slug'))->slug()->toString() : null,
        ]);
    }
}

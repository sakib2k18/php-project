<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\GalleryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalleryItemRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        $item = $this->route('gallery');

        return $item
            ? ($this->user()?->can('update', $item) ?? false)
            : ($this->user()?->can('create', GalleryItem::class) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'caption' => ['nullable', 'string', 'max:500'],
            'category' => ['required', Rule::in(array_keys(config('site.gallery_categories')))],
            'taken_on' => ['nullable', 'date', 'before_or_equal:today'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['nullable', 'boolean'],
            // The image is mandatory when creating, optional when editing.
            'image' => $this->imageRules(required: ! $this->route('gallery')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages() + [
            'image.required' => 'Please choose an image to add to the gallery.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'sort_order' => $this->input('sort_order') ?: 0,
        ]);
    }
}

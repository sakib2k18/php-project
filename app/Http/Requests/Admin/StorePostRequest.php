<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesImages;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    use ValidatesImages;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', Rule::unique('posts', 'slug')->withoutTrashed()],
            'excerpt' => ['required', 'string', 'min:20', 'max:500'],
            'content' => ['required', 'string', 'min:80'],
            'category' => ['required', Rule::in(array_keys(config('site.post_categories')))],
            'author' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(array_keys(config('site.post_statuses')))],
            'featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'cover_image' => $this->imageRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->imageMessages('cover_image');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'featured' => $this->boolean('featured'),
            'slug' => $this->filled('slug') ? str($this->input('slug'))->slug()->toString() : null,
        ]);
    }
}

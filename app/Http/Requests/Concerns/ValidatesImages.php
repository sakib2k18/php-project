<?php

namespace App\Http\Requests\Concerns;

/**
 * One definition of "a safe image upload", reused by every form request.
 *
 * The rules check the real MIME type (`mimetypes`), the extension (`mimes`),
 * the byte size and the pixel dimensions — a file has to satisfy all four.
 */
trait ValidatesImages
{
    /**
     * @return array<int, mixed>
     */
    protected function imageRules(bool $required = false): array
    {
        return array_filter([
            $required ? 'required' : 'nullable',
            'image',
            'mimes:'.implode(',', config('site.uploads.mimes')),
            'mimetypes:'.implode(',', config('site.uploads.mime_types')),
            'max:'.config('site.uploads.max_kb'),
            'dimensions:max_width='.config('site.uploads.max_dimension').',max_height='.config('site.uploads.max_dimension'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function imageMessages(string $attribute = 'image'): array
    {
        $maxMb = round(((int) config('site.uploads.max_kb')) / 1024, 1);

        return [
            "{$attribute}.image" => 'The file must be a valid image.',
            "{$attribute}.mimes" => 'Only JPG, JPEG, PNG and WEBP images are accepted.',
            "{$attribute}.mimetypes" => 'That file is not a real image. Only JPG, PNG and WEBP are accepted.',
            "{$attribute}.max" => "The image may not be larger than {$maxMb} MB.",
            "{$attribute}.dimensions" => 'The image is too large. Maximum '.config('site.uploads.max_dimension').'×'.config('site.uploads.max_dimension').' pixels.',
        ];
    }
}

<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves a stored relative path (e.g. "campaigns/abc.webp") into a public URL.
 * When no file is stored the accessor returns null and the UI falls back to the
 * generated <x-ui.media> placeholder.
 */
trait HasCoverImage
{
    public function imageColumn(): string
    {
        return 'image';
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->{$this->imageColumn()};

        if (blank($path)) {
            return null;
        }

        return Storage::disk(config('site.uploads.disk'))->url($path);
    }
}

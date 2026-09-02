<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded images safely.
 *
 * The original client filename is never trusted: the extension is derived from
 * the detected MIME type and the stored name is a random 40-char string, so a
 * file called "invoice.php.jpg" can never land on disk as executable code.
 */
class ImageUploadService
{
    /**
     * @return string|null Relative path on the configured disk (e.g. "campaigns/ab12….webp")
     */
    public function store(?UploadedFile $file, string $directory, ?string $replacing = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            return $replacing;
        }

        $extension = $this->extensionFor($file);

        if ($extension === null) {
            return $replacing;
        }

        $name = Str::random(40).'.'.$extension;
        $path = $file->storeAs($directory, $name, ['disk' => $this->disk()]);

        if ($path === false) {
            return $replacing;
        }

        $this->delete($replacing);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk($this->disk());

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Extension is resolved from the *detected* MIME type, not from the name.
     */
    protected function extensionFor(UploadedFile $file): ?string
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
    }

    protected function disk(): string
    {
        return config('site.uploads.disk', 'public');
    }
}

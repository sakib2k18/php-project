<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;

/**
 * Builds genuinely valid image files for upload tests.
 *
 * Laravel's UploadedFile::fake()->image() needs the GD extension, which is not
 * enabled by default in a stock XAMPP CLI. Writing a real (tiny) PNG or JPEG
 * byte string instead keeps the upload-validation tests running everywhere,
 * because the rules under test — `image`, `mimes`, `mimetypes` and `dimensions`
 * — all read the file itself rather than the client-supplied name.
 */
trait MakesTestImages
{
    /** A valid 2x2 PNG. */
    protected function pngUpload(string $name = 'photo.png'): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFElEQVR42mP8z8BQz0AEYBxVSF+FABJADveWkH6oAAAAAElFTkSuQmCC'
        );

        return $this->uploadFromBytes($bytes, $name, 'image/png');
    }

    /** A valid 1x1 JPEG. */
    protected function jpegUpload(string $name = 'photo.jpg'): UploadedFile
    {
        $bytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a'
            .'HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAA'
            .'AAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=='
        );

        return $this->uploadFromBytes($bytes, $name, 'image/jpeg');
    }

    protected function uploadFromBytes(string $bytes, string $name, string $mime): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'kt-test-image');
        file_put_contents($path, $bytes);

        // test: true so the file is accepted without a real HTTP upload.
        return new UploadedFile($path, $name, $mime, null, true);
    }
}

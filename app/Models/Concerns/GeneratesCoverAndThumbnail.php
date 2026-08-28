<?php

namespace App\Models\Concerns;

use App\Http\Controllers\ArticleImageController;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Auto-generates a branded cover image (when none was uploaded) and a
 * cropped thumbnail derived from it, so every Post/Job gets both without
 * requiring manual uploads.
 */
trait GeneratesCoverAndThumbnail
{
    protected function generateCoverIfMissing(string $directory, string $badge = ''): void
    {
        if (!empty($this->image)) {
            return;
        }

        try {
            $this->image = (new ArticleImageController())->generate($this->title, $directory, $badge);
        } catch (\Throwable $e) {
            Log::error(static::class . ' cover image generation failed: ' . $e->getMessage());
        }
    }

    protected function generateThumb(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $source = storage_path('app/public/' . $url);
        if (!is_file($source)) {
            return;
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($source);
            $image->cover(500, 300);

            $destination = storage_path('app/public/thumb/' . $url);
            $destinationDir = dirname($destination);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            $image->save($destination);
        } catch (\Throwable $e) {
            Log::error(static::class . ' thumbnail generation failed: ' . $e->getMessage());
        }
    }
}

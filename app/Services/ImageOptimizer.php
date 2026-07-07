<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ImageOptimizer
{
    /**
     * Redimensionne (sans jamais agrandir) et recompresse une image uploadée,
     * puis la stocke sur le disque donné. Retourne le chemin relatif stocké,
     * au même format que UploadedFile::store().
     */
    public static function storeResized(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxDimension = 1600,
        int $quality = 82,
    ): string {
        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown($maxDimension, $maxDimension);

        $mediaType = $image->origin()->mediaType();

        [$encoded, $extension] = match (true) {
            str_contains($mediaType, 'jpeg') => [$image->encodeUsingFormat(Format::JPEG, quality: $quality), 'jpg'],
            str_contains($mediaType, 'webp') => [$image->encodeUsingFormat(Format::WEBP, quality: $quality), 'webp'],
            str_contains($mediaType, 'png')  => [$image->encode(), 'png'],
            str_contains($mediaType, 'gif')  => [$image->encode(), 'gif'],
            default => [$image->encode(), $file->extension() ?: 'jpg'],
        };

        $path = trim($directory, '/').'/'.bin2hex(random_bytes(16)).'.'.$extension;
        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}

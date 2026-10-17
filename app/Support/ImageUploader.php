<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploader
{
    private const MAX_WIDTH = 1600;
    private const QUALITY = 80;

    /**
     * Store an uploaded image on the public disk, resized and converted to WebP.
     * Falls back to a plain store() when GD/WebP is unavailable or the image can't be processed.
     */
    public static function store(UploadedFile $file, string $directory): string|false
    {
        $webp = self::toWebp($file);

        if ($webp === null) {
            return $file->store($directory, 'public');
        }

        $path = trim($directory, '/') . '/' . Str::random(40) . '.webp';

        return Storage::disk('public')->put($path, $webp) ? $path : false;
    }

    private static function toWebp(UploadedFile $file): ?string
    {
        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $raw = @file_get_contents($file->getRealPath());
        $src = $raw === false ? false : @imagecreatefromstring($raw);
        if (!$src) {
            return null;
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getRealPath());
            $angle = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 0] ?? 0;
            if ($angle !== 0 && ($rotated = imagerotate($src, $angle, 0))) {
                $src = $rotated;
            }
        }

        $width = imagesx($src);
        $height = imagesy($src);
        if ($width > self::MAX_WIDTH) {
            $scaled = imagescale($src, self::MAX_WIDTH);
            if ($scaled) {
                $src = $scaled;
            }
        }

        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);

        ob_start();
        $ok = imagewebp($src, null, self::QUALITY);
        $data = ob_get_clean();

        return $ok && $data !== '' ? $data : null;
    }
}

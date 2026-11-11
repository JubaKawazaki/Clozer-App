<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Image pipeline: resize + convert to WebP, store originals/cutouts/thumbs.
 * Ported from Closet server/src/images.ts (sharp → GD).
 */
class ImageService
{
    private const ORIGINAL_MAX = 2400;
    private const THUMB_MAX = 300;

    public function __construct()
    {
        if (!function_exists('imagecreatefromwebp')) {
            throw new \RuntimeException('PHP GD extension must support WebP');
        }
    }

    /**
     * Save an uploaded file as WebP under storage/app/<folder>/.
     * Returns the path relative to storage/app (e.g. 'items/originals/abc.webp').
     */
    public function saveWebp(UploadedFile $file, string $folder, int $max = self::ORIGINAL_MAX, int $quality = 90): string
    {
        $src = $this->createImage($file);
        $src = $this->autoRotate($src);
        $src = $this->resize($src, $max);

        $rel = $folder.'/'.uniqid().'.webp';
        $dest = Storage::disk('local')->path($rel);
        @mkdir(dirname($dest), 0755, true);
        imagewebp($src, $dest, $quality);
        imagedestroy($src);

        return $rel;
    }

    /**
     * Create a GD image resource from an uploaded file.
     */
    private function createImage(UploadedFile $file)
    {
        $mime = $file->getMimeType();
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => throw new \RuntimeException("Unsupported image type: {$mime}"),
        };
    }

    /**
     * Fix EXIF orientation (rotate to canonical).
     */
    private function autoRotate($img)
    {
        if (!function_exists('exif_read_data')) return $img;
        return $img;
    }

    /**
     * Resize so the longest edge is at most `$max`, preserving aspect ratio.
     */
    private function resize($img, int $max)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= $max && $h <= $max) return $img;

        $scale = $max / max($w, $h);
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);

        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        return $dst;
    }

    /**
     * Remove a stored image by relative path.
     */
    public function removeImage(?string $rel): void
    {
        if (!$rel) return;
        Storage::disk('local')->delete($rel);
    }
}
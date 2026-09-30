<?php

namespace App\Services;

use finfo;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores media outlet posters: verifies the file is a real image (server-side MIME detection,
 * not the client's claim), resizes it to the hero target width, re-encodes as WebP under a
 * generated filename, and returns the stored path. The original file is never kept.
 */
class PosterImage
{
    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @return array{path: string, width: int, height: int}
     */
    public function store(UploadedFile $file, string $slug): array
    {
        // Detect from the bytes (never the extension or the client's claim).
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
        };

        if (! $source instanceof GdImage) {
            throw new RuntimeException('The image could not be read. Please upload a valid JPG, PNG or WebP file.');
        }

        $config = config('vmnewswire.posters');
        $image = $this->resize($source, $config['max_width']);
        imagedestroy($source);

        ob_start();
        $ok = imagewebp($image, null, $config['quality']);
        $binary = ob_get_clean();
        $width = imagesx($image);
        $height = imagesy($image);
        imagedestroy($image);

        if (! $ok || $binary === false || $binary === '') {
            throw new RuntimeException('The image could not be processed.');
        }

        // Never derived from the original filename.
        $path = $config['directory'].'/'.Str::slug($slug).'-'.Str::lower(Str::random(10)).'.webp';
        Storage::disk($config['disk'])->put($path, $binary);

        return ['path' => $path, 'width' => $width, 'height' => $height];
    }

    /**
     * Deletes a stored poster. Only paths inside the posters directory are touched.
     */
    public function delete(?string $path): void
    {
        $directory = config('vmnewswire.posters.directory').'/';

        if ($path && str_starts_with($path, $directory) && ! str_contains($path, '..')) {
            Storage::disk(config('vmnewswire.posters.disk'))->delete($path);
        }
    }

    private function resize(GdImage $source, int $maxWidth): GdImage
    {
        $w = imagesx($source);
        $h = imagesy($source);

        if ($w <= $maxWidth) {
            $target = imagecreatetruecolor($w, $h);
        } else {
            $target = imagecreatetruecolor($maxWidth, (int) round($h * $maxWidth / $w));
        }

        // Keep PNG/WebP transparency.
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));

        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $w, $h);

        return $target;
    }
}

<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;

/**
 * Convierte a WebP y comprime las imágenes que se suben al sistema (panel y API).
 *
 * - Corrige la orientación EXIF de las fotos de celular y elimina los metadatos.
 * - Reduce el lado mayor a MAX_SIDE px (nunca agranda).
 * - Conserva la transparencia de los PNG.
 */
class ImageOptimizer
{
    public const MAX_SIDE = 1200;

    public const QUALITY = 80;

    /** Megapíxeles máximos que se aceptan (evita agotar la memoria al decodificar). */
    public const MAX_MEGAPIXELS = 60;

    /**
     * Optimiza y guarda la imagen; devuelve la ruta relativa al disco (p. ej. "funkomacetas/abc.webp").
     */
    public static function store(UploadedFile|TemporaryUploadedFile $file, string $directory = 'funkomacetas', string $disk = 'public'): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.webp';

        try {
            $webp = static::toWebp((string) file_get_contents($file->getRealPath()));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['image' => $e->getMessage()]);
        }

        Storage::disk($disk)->put($path, $webp, 'public');

        return $path;
    }

    /**
     * Devuelve el contenido WebP optimizado de una imagen (jpeg, png, gif, webp o heic/heif).
     */
    public static function toWebp(string $binary, int $maxSide = self::MAX_SIDE, int $quality = self::QUALITY): string
    {
        $size = @getimagesizefromstring($binary);
        if ($size !== false && self::MAX_MEGAPIXELS * 1_000_000 < $size[0] * $size[1]) {
            throw new RuntimeException('La imagen es demasiado grande (máximo '.self::MAX_MEGAPIXELS.' megapíxeles).');
        }

        $image = static::decode($binary);

        $image = static::orient($image, $binary);
        $image = static::fit($image, $maxSide);

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, $quality);
        $webp = (string) ob_get_clean();

        if (! $ok || $webp === '') {
            throw new RuntimeException('No se pudo convertir la imagen a WebP.');
        }

        return $webp;
    }

    protected static function decode(string $binary): GdImage
    {
        $image = @imagecreatefromstring($binary);

        // HEIC/HEIF (fotos de iPhone sin convertir): GD no los lee, Imagick sí cuando está disponible.
        if ($image === false && class_exists(\Imagick::class)) {
            try {
                $imagick = new \Imagick;
                $imagick->readImageBlob($binary);
                $imagick->setImageFormat('png');
                $image = @imagecreatefromstring($imagick->getImageBlob());
            } catch (\Throwable) {
                $image = false;
            }
        }

        if ($image === false) {
            throw new RuntimeException('El archivo no es una imagen válida o su formato no es compatible.');
        }

        return $image;
    }

    protected static function orient(GdImage $image, string $binary): GdImage
    {
        if (! function_exists('exif_read_data') || ! str_starts_with($binary, "\xFF\xD8")) {
            return $image;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($binary));

        $rotated = match ($exif['Orientation'] ?? 1) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated ?: $image;
    }

    protected static function fit(GdImage $image, int $maxSide): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = $maxSide / max($width, $height);

        if ($scale >= 1) {
            return $image;
        }

        return imagescale($image, (int) round($width * $scale), (int) round($height * $scale), IMG_BICUBIC) ?: $image;
    }
}

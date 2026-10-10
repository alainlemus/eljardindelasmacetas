<?php

namespace App\Support;

use App\Models\Figure;
use GdImage;
use Illuminate\Support\Facades\Storage;

/**
 * Tarjeta de vista previa (1200×630 JPEG) para compartir una figura por WhatsApp, Facebook,
 * X, etc. Las redes piden JPEG/PNG de poco peso (WhatsApp: < 300 KB) y las fotos de las figuras
 * son WebP pequeñas, así que se genera una tarjeta con la foto, el nombre y el precio.
 *
 * Se guarda en disco con la fecha de la figura en el nombre: si la figura cambia, se regenera.
 */
class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    private const FONT_DISPLAY = 'Fredoka_600SemiBold.ttf';

    private const FONT_BOLD = 'Nunito_700Bold.ttf';

    private const FONT_SEMI = 'Nunito_600SemiBold.ttf';

    /** Ruta absoluta del JPEG de la figura (lo genera si hace falta). */
    public static function path(Figure $figure): string
    {
        $disk = Storage::disk('local');
        $file = sprintf('og/%s-%s.jpg', $figure->slug, md5($figure->updated_at?->timestamp.'|'.$figure->image.'|'.$figure->price.'|'.$figure->name));

        if (! $disk->exists($file)) {
            foreach ($disk->files('og') as $old) {
                if (str_starts_with(basename($old), $figure->slug.'-')) {
                    $disk->delete($old); // versiones anteriores de esta figura
                }
            }
            $disk->put($file, static::render($figure));
        }

        return $disk->path($file);
    }

    public static function render(Figure $figure): string
    {
        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imageantialias($im, true);

        $cream = imagecolorallocate($im, 255, 250, 240);
        $leaf = imagecolorallocate($im, 58, 127, 48);
        $leafLight = imagecolorallocate($im, 76, 154, 63);
        $ink = imagecolorallocate($im, 74, 44, 26);
        $berry = imagecolorallocate($im, 217, 58, 53);
        $sun = imagecolorallocate($im, 246, 185, 59);
        $white = imagecolorallocate($im, 255, 255, 255);
        $muted = imagecolorallocate($im, 138, 116, 102);

        imagefilledrectangle($im, 0, 0, self::WIDTH, self::HEIGHT, $cream);

        // Panel verde a la derecha con círculos decorativos
        imagefilledrectangle($im, 700, 0, self::WIDTH, self::HEIGHT, $leaf);
        imagefilledellipse($im, 1120, 90, 300, 300, imagecolorallocatealpha($im, 255, 255, 255, 112));
        imagefilledellipse($im, 760, 590, 220, 220, imagecolorallocatealpha($im, 246, 185, 59, 100));
        imagefilledellipse($im, 1150, 560, 140, 140, imagecolorallocatealpha($im, 217, 58, 53, 105));

        // Foto de la figura con marco blanco
        $size = 404;
        $x = 748;
        $y = (int) ((self::HEIGHT - $size) / 2);
        imagefilledrectangle($im, $x - 14, $y - 14, $x + $size + 14, $y + $size + 14, $white);
        $photo = static::loadPhoto($figure);
        if ($photo) {
            imagecopyresampled($im, $photo, $x, $y, 0, 0, $size, $size, imagesx($photo), imagesy($photo));
        } else {
            imagefilledrectangle($im, $x, $y, $x + $size, $y + $size, imagecolorallocate($im, 255, 243, 220));
            if ($logo = static::loadLogo()) {
                imagecopyresampled($im, $logo, $x + 62, $y + 62, 0, 0, 280, 280, imagesx($logo), imagesy($logo));
            }
        }

        // Marca (logo + nombre del sitio)
        if ($logo = static::loadLogo()) {
            imagecopyresampled($im, $logo, 56, 44, 0, 0, 74, 74, imagesx($logo), imagesy($logo));
        }
        static::text($im, 28, 146, 82, $leaf, self::FONT_DISPLAY, 'El Jardín');
        static::text($im, 20, 146, 112, $muted, self::FONT_SEMI, 'de las Macetas');

        // Categoría
        $top = 200;
        if ($figure->category) {
            static::text($im, 20, 56, $top, $leafLight, self::FONT_BOLD, mb_strtoupper($figure->category->name));
            $top += 14;
        }

        // Nombre (hasta 3 líneas)
        $lines = static::wrap($figure->name, self::FONT_DISPLAY, 50, 590, 3);
        $lineY = $top + 62;
        foreach ($lines as $line) {
            static::text($im, 50, 56, $lineY, $ink, self::FONT_DISPLAY, $line);
            $lineY += 64;
        }

        // Precio
        if ($figure->price > 0) {
            static::text($im, 54, 56, $lineY + 24, $berry, self::FONT_DISPLAY, $figure->formatted_price);
            $lineY += 74;
        }

        // Llamada a la acción
        $btnY = max($lineY + 14, 470);
        static::roundedRect($im, 56, $btnY, 420, 64, 32, $sun);
        static::text($im, 24, 84, $btnY + 42, $ink, self::FONT_BOLD, 'Pídela por WhatsApp');

        ob_start();
        imagejpeg($im, null, 84);

        return (string) ob_get_clean();
    }

    private static function loadPhoto(Figure $figure): ?GdImage
    {
        $path = $figure->image;
        if (! $path || str_starts_with($path, 'http')) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $src = @imagecreatefromstring($disk->get($path));
        if (! $src) {
            return null;
        }

        // Recorte cuadrado centrado
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $square = imagecreatetruecolor($side, $side);
        imagecopy($square, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2), $side, $side);

        return $square;
    }

    private static function loadLogo(): ?GdImage
    {
        $file = public_path('images/logo.png');
        $logo = is_file($file) ? @imagecreatefrompng($file) : false;
        if ($logo) {
            imagealphablending($logo, true);
        }

        return $logo ?: null;
    }

    private static function fontPath(string $font): string
    {
        return resource_path('fonts/'.$font);
    }

    private static function text(GdImage $im, float $size, int $x, int $y, int $color, string $font, string $text): void
    {
        imagettftext($im, $size, 0, $x, $y, $color, static::fontPath($font), $text);
    }

    /** @return list<string> */
    private static function wrap(string $text, string $font, float $size, int $maxWidth, int $maxLines): array
    {
        $lines = [];
        $current = '';

        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $try = $current === '' ? $word : $current.' '.$word;
            $box = imagettfbbox($size, 0, static::fontPath($font), $try);
            if (($box[2] - $box[0]) <= $maxWidth || $current === '') {
                $current = $try;
            } else {
                $lines[] = $current;
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], ' ,.').'…';
        }

        return $lines;
    }

    private static function roundedRect(GdImage $im, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }
}

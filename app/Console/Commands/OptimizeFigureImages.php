<?php

namespace App\Console\Commands;

use App\Models\Figure;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeFigureImages extends Command
{
    protected $signature = 'figures:optimize-images {--dry-run : Solo muestra lo que se convertiría}';

    protected $description = 'Convierte a WebP y comprime las fotos ya subidas de las figuras';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $before = $after = $converted = 0;

        foreach (Figure::query()->get() as $figure) {
            $paths = collect([$figure->image, ...($figure->images ?? [])])->filter();
            $map = [];

            foreach ($paths as $old) {
                if (str_ends_with(strtolower($old), '.webp') || str_starts_with($old, 'http') || ! $disk->exists($old)) {
                    continue;
                }

                $this->line("{$figure->sku}: {$old}");
                $size = $disk->size($old);
                $before += $size;

                if ($this->option('dry-run')) {
                    continue;
                }

                try {
                    $webp = ImageOptimizer::toWebp($disk->get($old));
                } catch (\Throwable $e) {
                    $this->warn("  omitida: {$e->getMessage()}");

                    continue;
                }

                $new = preg_replace('/\.[^.\/]+$/', '', $old).'.webp';
                $disk->put($new, $webp, 'public');
                $disk->delete($old);
                $map[$old] = $new;
                $after += strlen($webp);
                $converted++;
            }

            if ($map) {
                $figure->update([
                    'image' => $map[$figure->image] ?? $figure->image,
                    'images' => collect($figure->images ?? [])->map(fn ($p) => $map[$p] ?? $p)->all(),
                ]);
            }
        }

        $this->info($this->option('dry-run')
            ? 'Simulación: '.round($before / 1048576, 1).' MB por convertir.'
            : "Convertidas: {$converted}. ".round($before / 1048576, 1).' MB → '.round($after / 1048576, 1).' MB.');

        return self::SUCCESS;
    }
}

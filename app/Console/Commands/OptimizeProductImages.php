<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizeProductImages extends Command
{
    protected $signature = 'media:optimize-products {--dry-run : Report changes without updating files or database records} {--quality=90 : WebP quality from 0 to 100}';

    protected $description = 'Convert stored product images to optimized WebP files';

    public function handle(): int
    {
        $quality = filter_var($this->option('quality'), FILTER_VALIDATE_INT);
        if ($quality === false || $quality < 0 || $quality > 100) {
            $this->error('The quality option must be an integer from 0 to 100.');

            return self::FAILURE;
        }

        $disk = Storage::disk('media');
        $images = ProductImage::query()->select(['id', 'product_id', 'path'])->cursor();
        $processed = 0;
        $skipped = 0;
        $failed = 0;
        $savedBytes = 0;

        foreach ($images as $image) {
            if (strtolower(pathinfo($image->path, PATHINFO_EXTENSION)) === 'webp') {
                $skipped++;

                continue;
            }

            $contents = $disk->get($image->path);
            $source = @imagecreatefromstring($contents);
            if ($source === false) {
                $this->error("Could not decode image #{$image->id}: {$image->path}");
                $failed++;

                continue;
            }

            ob_start();
            $encoded = imagewebp($source, null, $quality);
            $optimized = ob_get_clean();
            imagedestroy($source);

            if (! $encoded || ! is_string($optimized) || $optimized === '') {
                $this->error("Could not encode image #{$image->id} as WebP.");
                $failed++;

                continue;
            }

            if (strlen($optimized) >= strlen($contents)) {
                $skipped++;

                continue;
            }

            $newPath = 'products/'.$image->product_id.'/'.Str::uuid().'.webp';
            $imageSavings = strlen($contents) - strlen($optimized);

            if ($this->option('dry-run')) {
                $this->line(sprintf(
                    'Would optimize %s -> %s (%.1f%% smaller)',
                    $image->path,
                    $newPath,
                    100 * $imageSavings / strlen($contents),
                ));
                $processed++;
                $savedBytes += $imageSavings;

                continue;
            }

            if (! $disk->put($newPath, $optimized)) {
                $this->error("Could not store optimized image #{$image->id}.");
                $failed++;

                continue;
            }

            $oldPath = $image->path;
            try {
                $image->update(['path' => $newPath]);
            } catch (\Throwable $exception) {
                $disk->delete($newPath);
                throw $exception;
            }

            $processed++;
            $savedBytes += $imageSavings;

            if (! $disk->delete($oldPath)) {
                $this->error("Updated image #{$image->id}, but could not remove its original file: {$oldPath}");
                $failed++;
            }
        }

        $this->info(sprintf(
            '%s %d image(s); skipped %d; estimated savings %.1f MB.',
            $this->option('dry-run') ? 'Would optimize' : 'Optimized',
            $processed,
            $skipped,
            $savedBytes / 1048576,
        ));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}

<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportEflixCatalog extends Command
{
    protected $signature = 'catalog:import-eflix
        {--url=https://eflix.store : Source WooCommerce site URL}
        {--images : Download category and product images}';

    protected $description = 'Import the public WooCommerce catalog, including categories, prices, descriptions and media';

    public function handle(): int
    {
        $baseUrl = rtrim((string) $this->option('url'), '/');
        $api = $baseUrl.'/wp-json/wc/store/v1';
        $this->info("Reading catalog from {$baseUrl}...");

        $categories = $this->importCategories($api);
        if ($categories === []) {
            $this->error('No product categories were returned by the source API.');

            return self::FAILURE;
        }

        $page = 1;
        $imported = 0;
        $lastPage = 1;

        do {
            $response = $this->get($api.'/products', ['per_page' => 100, 'page' => $page]);
            if ($response === null) {
                $this->error("Unable to read product page {$page}.");

                return self::FAILURE;
            }

            foreach ($response->json() as $sourceProduct) {
                $this->importProduct($sourceProduct, $categories);
                $imported++;
                $this->output->write("\rImported {$imported} products");
            }

            $lastPage = (int) $response->header('X-WP-TotalPages', $page);
            $page++;
        } while ($page <= $lastPage);

        $this->newLine();
        $this->info("Catalog import completed: {$imported} products.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, Category>
     */
    private function importCategories(string $api): array
    {
        $response = $this->get($api.'/products/categories', ['per_page' => 100]);
        if ($response === null) {
            return [];
        }

        $categories = [];
        $parents = [];
        foreach ($response->json() as $sourceCategory) {
            $name = trim(strip_tags((string) ($sourceCategory['name'] ?? '')));
            if ($name === '') {
                continue;
            }

            $category = Category::updateOrCreate(
                ['slug' => $this->slug((string) ($sourceCategory['slug'] ?? $name), (int) $sourceCategory['id'])],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => $this->cleanText((string) ($sourceCategory['description'] ?? '')),
                    'sort_order' => (int) ($sourceCategory['menu_order'] ?? $sourceCategory['id'] ?? 0),
                    'is_active' => true,
                    'is_featured' => false,
                    'meta_title' => $name,
                    'meta_description' => 'Browse '.$name.' products.',
                ],
            );

            $categories[(int) $sourceCategory['id']] = $category;
            $parents[(int) $sourceCategory['id']] = (int) ($sourceCategory['parent'] ?? 0);

            if ($this->option('images') && filled(data_get($sourceCategory, 'image.src'))) {
                $path = $this->downloadMedia(
                    (string) data_get($sourceCategory, 'image.src'),
                    'categories/'.$category->id,
                    'eflix-'.(int) $sourceCategory['id'],
                );
                if ($path !== null) {
                    $category->update(['image_path' => $path]);
                }
            }
        }

        foreach ($parents as $sourceId => $parentSourceId) {
            if (isset($categories[$sourceId])) {
                $categories[$sourceId]->update([
                    'parent_id' => $categories[$parentSourceId]->id ?? null,
                ]);
            }
        }

        return $categories;
    }

    /**
     * @param  array<int, Category>  $categories
     */
    private function importProduct(array $sourceProduct, array $categories): void
    {
        $name = trim(strip_tags((string) ($sourceProduct['name'] ?? '')));
        $sourceId = (int) ($sourceProduct['id'] ?? 0);
        if ($name === '' || $sourceId === 0) {
            return;
        }

        $category = $categories[(int) data_get($sourceProduct, 'categories.0.id')] ?? reset($categories);
        $prices = (array) ($sourceProduct['prices'] ?? []);
        $regularPrice = $this->minorUnitsToPrice($prices['regular_price'] ?? $prices['price'] ?? '0');
        $salePrice = $this->minorUnitsToPrice($prices['sale_price'] ?? null);
        $product = Product::updateOrCreate(
            ['sku' => 'EFLIX-'.$sourceId],
            [
                'uuid' => (string) Str::uuid(),
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $this->slug((string) ($sourceProduct['slug'] ?? $name), $sourceId),
                'product_type' => 'simple',
                'unit' => 'piece',
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice !== null && $salePrice < $regularPrice ? $salePrice : null,
                'description' => $this->cleanText((string) ($sourceProduct['description'] ?? '')) ?: $name,
                'short_description' => $this->cleanText((string) ($sourceProduct['short_description'] ?? '')) ?: $name,
                'status' => 'published',
                'featured' => false,
                'published_at' => now(),
                'meta_title' => $name,
                'meta_description' => $name,
            ],
        );

        if (! $this->option('images')) {
            return;
        }

        foreach ((array) ($sourceProduct['images'] ?? []) as $sortOrder => $sourceImage) {
            $imageUrl = (string) ($sourceImage['src'] ?? '');
            if ($imageUrl === '') {
                continue;
            }

            $mediaId = (int) ($sourceImage['id'] ?? $sortOrder);
            $path = $this->downloadMedia($imageUrl, 'products/'.$product->id, 'eflix-'.$mediaId);
            if ($path === null) {
                continue;
            }

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'path' => $path],
                [
                    'alt_text' => $name,
                    'title' => $name,
                    'is_thumbnail' => $sortOrder === 0,
                    'sort_order' => (int) $sortOrder,
                ],
            );
        }
    }

    private function get(string $url, array $query): ?Response
    {
        $response = Http::acceptJson()
            ->withUserAgent('eflix Entrepreneur Platform catalog importer')
            ->timeout(60)
            ->retry(5, 5000, fn (\Throwable $exception): bool => $exception instanceof \Illuminate\Http\Client\RequestException
                && $exception->response?->status() === 429)
            ->get($url, $query);
        if ($response->failed()) {
            $this->error("Source API request failed: HTTP {$response->status()} ({$url}).");

            return null;
        }

        return $response;
    }

    private function downloadMedia(string $url, string $directory, string $filename): ?string
    {
        $response = Http::withUserAgent('eflix Entrepreneur Platform catalog importer')
            ->timeout(60)
            ->retry(5, 5000, fn (\Throwable $exception): bool => $exception instanceof \Illuminate\Http\Client\RequestException
                && $exception->response?->status() === 429)
            ->get($url);
        if ($response->failed()) {
            $this->warn("Skipping unavailable image: {$url}");

            return null;
        }

        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true) ? $extension : 'jpg';
        $path = $directory.'/'.$filename.'.'.$extension;
        if (! Storage::disk('media')->put($path, $response->body())) {
            $this->error("Unable to save image: {$path}");

            return null;
        }

        return $path;
    }

    private function minorUnitsToPrice(mixed $value): float
    {
        return is_numeric($value) ? ((float) $value / 100) : 0.0;
    }

    private function cleanText(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($value))));
    }

    private function slug(string $value, int $fallbackId): string
    {
        $slug = Str::slug(rawurldecode($value));

        return $slug !== '' ? $slug : 'eflix-'.$fallbackId;
    }
}

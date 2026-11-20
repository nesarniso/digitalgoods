<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductVideo;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportWorklincProducts extends Command
{
    protected $signature = 'worklinc:import-products';

    protected $description = 'Import all public WorkLinc products, prices, descriptions, categories and images';

    private const API_URL = 'https://shop.worklinc.com/wp-json/wc/store/v1/products';

    public function handle(): int
    {
        $this->info('Reading WorkLinc products...');
        $sourceProducts = $this->fetchProducts();
        $categories = Category::query()->where('is_active', true)->get()->keyBy('slug');
        $categoriesById = $categories->keyBy('id');

        if ($categories->isEmpty()) {
            $this->error('No active local categories exist. Import WorkLinc categories first.');

            return self::FAILURE;
        }

        $this->validateProducts($sourceProducts, $categories);

        $importedProducts = 0;
        $importedImages = 0;
        $importedVideos = 0;
        $failedImages = 0;
        $failedVideos = 0;
        $fallbackCategoryProducts = 0;

        foreach ($sourceProducts as $sourceProduct) {
            if (($sourceProduct['categories'] ?? []) === []) {
                $fallbackCategoryProducts++;
            }
            $product = $this->saveProduct($sourceProduct, $categories, $categoriesById);
            $importedProducts++;
            $imagePaths = [];
            $productImageFailed = false;

            foreach ((array) ($sourceProduct['images'] ?? []) as $sortOrder => $sourceImage) {
                try {
                    $imagePaths[] = $this->saveImage($product, $sourceImage, (int) $sortOrder);
                    $importedImages++;
                } catch (ConnectionException|RequestException|RuntimeException $exception) {
                    $this->warn("Could not import image for {$product->name}: {$exception->getMessage()}");
                    $failedImages++;
                    $productImageFailed = true;
                }
            }

            if (! $productImageFailed) {
                $this->removeStaleImages($product, $imagePaths);
            }

            $videoUrls = $this->extractVideoUrls(
                (string) ($sourceProduct['description'] ?? '')."\n".(string) ($sourceProduct['short_description'] ?? ''),
            );
            $videoPaths = [];
            $productVideoFailed = false;
            foreach ($videoUrls as $sortOrder => $videoUrl) {
                try {
                    $videoPaths[] = $this->saveVideo($product, $videoUrl, $sortOrder);
                    $importedVideos++;
                } catch (ConnectionException|RequestException|RuntimeException $exception) {
                    $this->warn("Could not import sample video for {$product->name}: {$exception->getMessage()}");
                    $failedVideos++;
                    $productVideoFailed = true;
                }
            }

            if (! $productVideoFailed) {
                $this->removeStaleVideos($product, $videoPaths);
            }
        }

        $this->newLine();
        $this->info("Imported {$importedProducts} of ".count($sourceProducts).' products, '.$importedImages.' images, and '.$importedVideos.' sample videos.');
        if ($fallbackCategoryProducts > 0) {
            $this->line("Assigned {$fallbackCategoryProducts} uncategorized products to Digital Products.");
        }

        if ($failedImages > 0 || $failedVideos > 0) {
            $this->error("{$failedImages} images and {$failedVideos} sample videos could not be imported. Re-run the command to retry them.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchProducts(): array
    {
        $products = [];
        $page = 1;
        $lastPage = 1;
        $expectedTotal = null;

        do {
            $response = Http::acceptJson()
                ->withUserAgent('WorkLinc catalog importer')
                ->timeout(60)
                ->retry(5, 1000, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response?->status() === 429))
                ->get(self::API_URL, ['per_page' => 100, 'page' => $page])
                ->throw();
            $payload = $response->json();

            if (! is_array($payload) || ! array_is_list($payload)) {
                throw new RuntimeException("WorkLinc returned an invalid product list on page {$page}.");
            }

            foreach ($payload as $product) {
                if (! is_array($product)) {
                    throw new RuntimeException("WorkLinc returned an invalid product record on page {$page}.");
                }

                $products[] = $product;
            }

            $lastPage = max($page, (int) $response->header('X-WP-TotalPages', $page));
            if ($page === 1) {
                $expectedTotal = (int) $response->header('X-WP-Total', 0);
            }
            $page++;
        } while ($page <= $lastPage);

        if ($products === []) {
            throw new RuntimeException('WorkLinc returned no products.');
        }

        if ($expectedTotal !== null && $expectedTotal > 0 && count($products) !== $expectedTotal) {
            throw new RuntimeException("WorkLinc reported {$expectedTotal} products but returned ".count($products).'.');
        }

        return $products;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @param  Collection<string, Category>  $categories
     */
    private function validateProducts(array $products, Collection $categories): void
    {
        $seenIds = [];
        $seenSlugs = [];

        foreach ($products as $product) {
            $sourceId = (int) ($product['id'] ?? 0);
            $name = $this->plainText((string) ($product['name'] ?? ''));
            $slug = Str::slug((string) ($product['slug'] ?? ''));

            if ($sourceId < 1 || $name === '' || $slug === '') {
                throw new RuntimeException('WorkLinc returned a product with a missing ID, name, or slug.');
            }

            if (isset($seenIds[$sourceId]) || isset($seenSlugs[$slug])) {
                throw new RuntimeException("WorkLinc returned a duplicate product ID or slug for {$slug}.");
            }
            $seenIds[$sourceId] = true;
            $seenSlugs[$slug] = true;

            if (($product['type'] ?? null) !== 'simple') {
                throw new RuntimeException("WorkLinc product {$sourceId} is not a simple product and needs a variant mapping.");
            }

            $prices = $product['prices'] ?? null;
            if (! is_array($prices) || ($prices['currency_code'] ?? null) !== 'INR') {
                throw new RuntimeException("WorkLinc product {$sourceId} does not have INR pricing.");
            }
            $minorUnit = (int) ($prices['currency_minor_unit'] ?? -1);
            if ($minorUnit < 0 || $minorUnit > 4 || ! is_numeric($prices['regular_price'] ?? $prices['price'] ?? null)) {
                throw new RuntimeException("WorkLinc product {$sourceId} has invalid price data.");
            }

            $sourceCategories = $product['categories'] ?? null;
            if (! is_array($sourceCategories)) {
                throw new RuntimeException("WorkLinc product {$sourceId} has an invalid categories list.");
            }
            if ($sourceCategories === []) {
                if (! $categories->has('digital-products')) {
                    throw new RuntimeException("WorkLinc product {$sourceId} has no categories and there is no Digital Products fallback.");
                }
            }
            foreach ($sourceCategories as $sourceCategory) {
                $slug = (string) ($sourceCategory['slug'] ?? '');
                if (! $categories->has($slug)) {
                    throw new RuntimeException("WorkLinc product {$sourceId} references missing local category '{$slug}'.");
                }
            }

            $images = $product['images'] ?? null;
            if (! is_array($images) || $images === []) {
                throw new RuntimeException("WorkLinc product {$sourceId} has no images.");
            }
            foreach ($images as $image) {
                $parts = is_array($image) ? parse_url((string) ($image['src'] ?? '')) : false;
                if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
                    || strtolower((string) ($parts['host'] ?? '')) !== 'shop.worklinc.com') {
                    throw new RuntimeException("WorkLinc product {$sourceId} has an invalid image URL.");
                }
            }

            foreach ($this->extractVideoUrls(
                (string) ($product['description'] ?? '')."\n".(string) ($product['short_description'] ?? ''),
            ) as $videoUrl) {
                $parts = parse_url($videoUrl);
                if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
                    || strtolower((string) ($parts['host'] ?? '')) !== 'shop.worklinc.com') {
                    throw new RuntimeException("WorkLinc product {$sourceId} has an invalid sample video URL.");
                }
            }
        }
    }

    /**
     * @param  Collection<string, Category>  $categories
     * @param  Collection<int, Category>  $categoriesById
     */
    private function saveProduct(array $sourceProduct, Collection $categories, Collection $categoriesById): Product
    {
        $sourceId = (int) $sourceProduct['id'];
        $sourceCategories = collect($sourceProduct['categories']);
        $assignedCategories = $sourceCategories
            ->map(fn (array $sourceCategory): ?Category => $categories->get((string) $sourceCategory['slug']))
            ->filter()
            ->unique('id')
            ->values();
        if ($assignedCategories->isEmpty()) {
            $assignedCategories = collect([$categories->get('digital-products')]);
        }
        $category = $assignedCategories
            ->sortByDesc(fn (Category $category): int => $this->categoryDepth($category, $categoriesById))
            ->first();

        if (! $category) {
            throw new RuntimeException("No local category could be selected for WorkLinc product {$sourceId}.");
        }

        $prices = $sourceProduct['prices'];
        $minorUnit = (int) $prices['currency_minor_unit'];
        $regularPrice = $this->minorUnitsToPrice($prices['regular_price'] ?: $prices['price'], $minorUnit);
        $salePrice = is_numeric($prices['sale_price'] ?? null)
            ? $this->minorUnitsToPrice($prices['sale_price'], $minorUnit)
            : null;
        if ($salePrice !== null && (bccomp($salePrice, '0.00', 2) <= 0 || bccomp($salePrice, $regularPrice, 2) >= 0)) {
            $salePrice = null;
        }

        $name = $this->plainText((string) $sourceProduct['name']);
        $description = $this->plainText((string) ($sourceProduct['description'] ?? ''));
        $shortDescription = $this->plainText((string) ($sourceProduct['short_description'] ?? ''));
        $slug = Str::slug((string) $sourceProduct['slug']);
        $sku = 'WORKLINC-'.$sourceId;

        $product = Product::withTrashed()->firstOrNew(['sku' => $sku]);
        if (! $product->exists) {
            $product->uuid = (string) Str::uuid();
        }

        $product->fill([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $slug,
            'product_type' => 'digital',
            'unit' => 'piece',
            'description' => $description !== '' ? $description : $shortDescription,
            'short_description' => $shortDescription !== '' ? $shortDescription : $description,
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'status' => 'published',
            'featured' => false,
            'published_at' => $product->published_at ?? now(),
            'meta_title' => Str::limit($name, 255, ''),
            'meta_description' => Str::limit($shortDescription ?: $description ?: $name, 255, ''),
        ]);
        $product->deleted_at = null;
        $product->save();
        $product->categories()->sync($assignedCategories->pluck('id'));

        return $product;
    }

    private function categoryDepth(Category $category, Collection $categoriesById): int
    {
        $depth = 0;
        $parentId = $category->parent_id;
        $visited = [];

        while ($parentId !== null && ! isset($visited[$parentId])) {
            $visited[$parentId] = true;
            $parent = $categoriesById->get($parentId);
            if (! $parent) {
                break;
            }
            $depth++;
            $parentId = $parent->parent_id;
        }

        return $depth;
    }

    /**
     * @param  array<string, mixed>  $sourceImage
     */
    private function saveImage(Product $product, array $sourceImage, int $sortOrder): string
    {
        $url = (string) ($sourceImage['src'] ?? '');
        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || strtolower((string) ($parts['host'] ?? '')) !== 'shop.worklinc.com') {
            throw new RuntimeException('The image URL is not on the WorkLinc HTTPS host.');
        }

        $extension = strtolower(pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION));
        $sourceImageId = (int) ($sourceImage['id'] ?? $sortOrder);
        $knownExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
        $disk = Storage::disk('media');
        $path = $knownExtension
            ? 'products/'.$product->id.'/worklinc-'.$sourceImageId.'-'.$sortOrder.'.'.$extension
            : '';

        if (! $knownExtension || ! $disk->exists($path)) {
            $response = Http::withUserAgent('WorkLinc catalog importer')
                ->timeout(60)
                ->retry(5, 1000, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response?->status() === 429))
                ->get($url)
                ->throw();

            $contentType = strtolower((string) $response->header('Content-Type'));
            if (! str_starts_with($contentType, 'image/')) {
                throw new RuntimeException("The source returned a non-image response ({$contentType}).");
            }

            $contents = $response->body();
            if ($contents === '' || strlen($contents) > 25 * 1024 * 1024) {
                throw new RuntimeException('The source image is empty or exceeds the 25 MB import limit.');
            }

            if (! $knownExtension) {
                $extension = match (strtok($contentType, ';')) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/avif' => 'avif',
                    default => 'webp',
                };
                $path = 'products/'.$product->id.'/worklinc-'.$sourceImageId.'-'.$sortOrder.'.'.$extension;
            }

            if (! $disk->put($path, $contents)) {
                throw new RuntimeException("Unable to save product image {$path}.");
            }
        }

        $altText = $this->plainText((string) ($sourceImage['alt'] ?? $product->name));
        ProductImage::updateOrCreate(
            ['product_id' => $product->id, 'path' => $path],
            [
                'alt_text' => Str::limit($altText, 255, ''),
                'title' => Str::limit($product->name, 255, ''),
                'is_thumbnail' => $sortOrder === 0,
                'sort_order' => $sortOrder,
            ],
        );

        return $path;
    }

    /**
     * @param  list<string>  $currentPaths
     */
    private function removeStaleImages(Product $product, array $currentPaths): void
    {
        $images = ProductImage::query()
            ->where('product_id', $product->id)
            ->where('path', 'like', 'products/'.$product->id.'/worklinc-%')
            ->get();

        foreach ($images as $image) {
            if (in_array($image->path, $currentPaths, true)) {
                continue;
            }

            if (! Storage::disk('media')->delete($image->path)) {
                throw new RuntimeException("Unable to remove stale product image {$image->path}.");
            }

            $image->delete();
        }
    }

    /**
     * @return list<string>
     */
    private function extractVideoUrls(string $html): array
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $pattern = '~https://shop\.worklinc\.com/[^\s"\'<>]*?\.(?:mp4|webm|mov|m4v)(?:\?[^\s"\'<>]*?)?(?=https?://|["\'\s<>]|$)~i';
        if (preg_match_all($pattern, $html, $matches) === false) {
            throw new RuntimeException('Unable to parse sample video URLs from the product description.');
        }

        return array_values(array_unique($matches[0]));
    }

    private function saveVideo(Product $product, string $url, int $sortOrder): string
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || strtolower((string) ($parts['host'] ?? '')) !== 'shop.worklinc.com') {
            throw new RuntimeException('The video URL is not on the WorkLinc HTTPS host.');
        }

        $extension = strtolower(pathinfo((string) ($parts['path'] ?? ''), PATHINFO_EXTENSION));
        if (! in_array($extension, ['mp4', 'webm', 'mov', 'm4v'], true)) {
            $extension = 'mp4';
        }

        $sourceHash = hash('sha256', $url);
        $path = 'product-videos/worklinc-'.$sourceHash.'.'.$extension;
        $disk = Storage::disk('media');
        $mimeType = match ($extension) {
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            default => 'video/mp4',
        };

        if (! $disk->exists($path)) {
            $encodedPath = implode('/', array_map(
                fn (string $segment): string => rawurlencode(rawurldecode($segment)),
                explode('/', (string) ($parts['path'] ?? '')),
            ));
            $requestUrl = 'https://shop.worklinc.com/'.$encodedPath;
            if (isset($parts['query'])) {
                $requestUrl .= '?'.$parts['query'];
            }

            $response = Http::accept('video/*')
                ->withUserAgent('WorkLinc catalog importer')
                ->timeout(90)
                ->retry(5, 1000, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response?->status() === 429))
                ->get($requestUrl)
                ->throw();

            $mimeType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            if (! str_starts_with($mimeType, 'video/')) {
                throw new RuntimeException("The source returned a non-video response ({$mimeType}).");
            }

            $contents = $response->body();
            if ($contents === '' || strlen($contents) > 100 * 1024 * 1024) {
                throw new RuntimeException('The source video is empty or exceeds the 100 MB import limit.');
            }
            if (! $disk->put($path, $contents)) {
                throw new RuntimeException("Unable to save sample video {$path}.");
            }
        }

        ProductVideo::updateOrCreate(
            ['product_id' => $product->id, 'source_hash' => $sourceHash],
            [
                'source_url' => $url,
                'path' => $path,
                'title' => 'Sample '.($sortOrder + 1),
                'mime_type' => $mimeType,
                'sort_order' => $sortOrder,
            ],
        );

        return $path;
    }

    /**
     * @param  list<string>  $currentPaths
     */
    private function removeStaleVideos(Product $product, array $currentPaths): void
    {
        $videos = ProductVideo::query()->where('product_id', $product->id)->get();

        foreach ($videos as $video) {
            if (in_array($video->path, $currentPaths, true)) {
                continue;
            }

            $isShared = ProductVideo::query()
                ->where('path', $video->path)
                ->where('id', '!=', $video->id)
                ->exists();
            if (! $isShared && Storage::disk('media')->exists($video->path)
                && ! Storage::disk('media')->delete($video->path)) {
                throw new RuntimeException("Unable to remove stale sample video {$video->path}.");
            }

            $video->delete();
        }
    }

    private function minorUnitsToPrice(mixed $value, int $minorUnit): string
    {
        return number_format(((float) $value) / (10 ** $minorUnit), $minorUnit, '.', '');
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        foreach ($this->extractVideoUrls($value) as $videoUrl) {
            $value = str_replace($videoUrl, '', $value);
        }
        $value = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6]|\/section)\b[^>]*>/i', "\n", $value) ?? $value;
        $value = strip_tags($value);
        $value = preg_replace('/[ \t]*\R[ \t]*/u', "\n", $value) ?? $value;
        $value = preg_replace('/\R{3,}/u', "\n\n", $value) ?? $value;

        return trim($value);
    }
}

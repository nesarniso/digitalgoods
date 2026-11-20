<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductVideo;
use App\Domain\Catalog\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Sole entry point for product mutation. `vendor_id` is accepted but stays
 * nullable/unused while multi_vendor is disabled — see Phase 0 decision.
 * When Phase 14 turns vendor mode on, this service is where "does this
 * actor own this product" enforcement gets added; it isn't needed yet
 * because there's only one seller.
 */
class ProductService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): Product
    {
        return DB::transaction(function () use ($data, $actor) {
            $this->assertPricingIsSane($data['regular_price'], $data['sale_price'] ?? null);
            $productType = $data['product_type'] ?? 'simple';
            $variants = $data['variants'] ?? [];

            $product = Product::create([
                'uuid' => (string) Str::uuid(),
                'vendor_id' => $data['vendor_id'] ?? null,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'product_type' => $productType,
                'download_url' => $data['download_url'] ?? null,
                'unit' => $data['unit'] ?? null,
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'entrepreneur_profit_percent' => $data['entrepreneur_profit_percent'] ?? null,
                'entrepreneur_profit_flat_rate' => $data['entrepreneur_profit_flat_rate'] ?? null,
                'regular_price' => $data['regular_price'],
                'sale_price' => $data['sale_price'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'weight' => $data['weight'] ?? null,
                'minimum_order_quantity' => $data['minimum_order_quantity'] ?? 1,
                'maximum_order_quantity' => $data['maximum_order_quantity'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'featured' => $data['featured'] ?? false,
                'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            if (! empty($data['tag_ids'])) {
                $product->tags()->sync($data['tag_ids']);
            }

            if ($variants !== []) {
                foreach ($variants as $variantData) {
                    $this->addVariant($product, $variantData, $actor, audit: false);
                }
            }

            $this->auditLogger->log(
                actor: $actor, action: 'created', module: 'Catalog',
                entityType: 'Product', entityId: (string) $product->id,
                newValue: ['name' => $product->name, 'sku' => $product->sku, 'status' => $product->status],
            );

            return $product->load(['variants.attributeValues', 'tags']);
        });
    }

    public function update(Product $product, array $data, User $actor): Product
    {
        $before = $product->only(['name', 'regular_price', 'sale_price', 'status']);

        if (array_key_exists('regular_price', $data) || array_key_exists('sale_price', $data)) {
            $this->assertPricingIsSane(
                $data['regular_price'] ?? $product->regular_price,
                $data['sale_price'] ?? $product->sale_price,
            );
        }

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if (($data['status'] ?? null) === 'published' && $product->status !== 'published') {
            $data['published_at'] = now();
        }

        $data['updated_by'] = $actor->id;

        $product->update($data);

        if (array_key_exists('tag_ids', $data)) {
            $product->tags()->sync($data['tag_ids']);
        }

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Catalog',
            entityType: 'Product', entityId: (string) $product->id,
            oldValue: $before,
            newValue: $product->only(['name', 'regular_price', 'sale_price', 'status']),
        );

        return $product;
    }

    public function delete(Product $product, User $actor): void
    {
        $product->delete();

        $this->auditLogger->log(
            actor: $actor, action: 'deleted', module: 'Catalog',
            entityType: 'Product', entityId: (string) $product->id,
        );
    }

    public function restore(Product $product, User $actor): Product
    {
        return DB::transaction(function () use ($product, $actor) {
            $product->restore();
            $product->variants()->withTrashed()->restore();
            $product->load('variants');

            $this->auditLogger->log(
                actor: $actor, action: 'restored', module: 'Catalog',
                entityType: 'Product', entityId: (string) $product->id,
                newValue: ['name' => $product->name, 'sku' => $product->sku],
            );

            return $product->fresh(['variants']);
        });
    }

    public function addVariant(Product $product, array $data, User $actor, bool $audit = true): ProductVariant
    {
        return DB::transaction(function () use ($product, $data, $actor, $audit) {
            if ($data['price'] !== null && isset($data['sale_price']) && $data['sale_price'] !== null) {
                $this->assertPricingIsSane($data['price'], $data['sale_price']);
            }

            $isDefault = (bool) ($data['is_default'] ?? false)
                || ! $product->variants()->exists();
            if ($isDefault) {
                $product->variants()->update(['is_default' => false]);
            }

            $variant = $product->variants()->create([
                'sku' => $this->variantSku($product, $data['sku'] ?? null),
                'barcode' => $data['barcode'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'price' => $data['price'],
                'sale_price' => $data['sale_price'] ?? null,
                'weight' => $data['weight'] ?? null,
                'is_default' => $isDefault,
            ]);

            $attributeValueIds = $this->normalizedAttributeValueIds($data['attribute_value_ids'] ?? []);
            $this->assertUniqueVariantCombination($product, $attributeValueIds);
            $variant->attributeValues()->sync($attributeValueIds);

            if ($audit) {
                $this->auditLogger->log(
                    actor: $actor, action: 'variant_created', module: 'Catalog',
                    entityType: 'ProductVariant', entityId: (string) $variant->id,
                    newValue: ['product_id' => $product->id, 'sku' => $variant->sku],
                );
            }

            return $variant;
        });
    }

    public function updateVariant(Product $product, ProductVariant $variant, array $data, User $actor): ProductVariant
    {
        abort_unless($variant->product_id === $product->id, 404);

        return DB::transaction(function () use ($product, $variant, $data, $actor) {
            $this->assertPricingIsSane($data['price'], $data['sale_price'] ?? null);
            $before = $variant->only(['sku', 'barcode', 'purchase_price', 'price', 'sale_price', 'weight', 'is_default']);

            if ((bool) ($data['is_default'] ?? false)) {
                $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
            }

            $variant->update([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? null,
                'price' => $data['price'],
                'sale_price' => $data['sale_price'] ?? null,
                'weight' => $data['weight'] ?? null,
                'is_default' => (bool) ($data['is_default'] ?? false),
            ]);
            if (! $variant->is_default && ! $product->variants()->where('is_default', true)->exists()) {
                $variant->update(['is_default' => true]);
            }
            $attributeValueIds = $this->normalizedAttributeValueIds($data['attribute_value_ids'] ?? []);
            $this->assertUniqueVariantCombination($product, $attributeValueIds, $variant->id);
            $variant->attributeValues()->sync($attributeValueIds);

            $this->auditLogger->log(
                actor: $actor, action: 'variant_updated', module: 'Catalog',
                entityType: 'ProductVariant', entityId: (string) $variant->id,
                oldValue: $before,
                newValue: $variant->only(array_keys($before)),
            );

            return $variant->fresh(['attributeValues']);
        });
    }

    public function deleteVariant(Product $product, ProductVariant $variant, User $actor): void
    {
        abort_unless($variant->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $variant, $actor) {
            if ($product->variants()->count() <= 1) {
                throw ValidationException::withMessages([
                    'variant' => ['A product must have at least one variant.'],
                ]);
            }

            $wasDefault = $variant->is_default;
            $variant->delete();

            if ($wasDefault && ! $product->variants()->where('is_default', true)->exists()) {
                $product->variants()->orderBy('id')->first()?->update(['is_default' => true]);
            }

            $this->auditLogger->log(
                actor: $actor, action: 'variant_deleted', module: 'Catalog',
                entityType: 'ProductVariant', entityId: (string) $variant->id,
                oldValue: ['product_id' => $product->id, 'sku' => $variant->sku],
            );
        });
    }

    private function variantSku(Product $product, ?string $sku): string
    {
        if (filled($sku)) {
            return $sku;
        }

        $base = Str::upper(Str::slug($product->sku ?: $product->name, '-'));
        $prefix = substr(str_replace('-', '', $base), 0, 60);
        $number = $product->variants()->withTrashed()->count() + 1;
        $candidate = "{$prefix}-VAR-{$number}";

        while (ProductVariant::withTrashed()->where('sku', $candidate)->exists()) {
            $number++;
            $candidate = "{$prefix}-VAR-{$number}";
        }

        return $candidate;
    }

    private function normalizedAttributeValueIds(array $ids): array
    {
        return collect($ids)->filter(fn ($id) => filled($id))->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
    }

    private function assertUniqueVariantCombination(Product $product, array $attributeValueIds, ?int $ignoreVariantId = null): void
    {
        if ($attributeValueIds === []) {
            return;
        }

        $values = \App\Domain\Catalog\Models\AttributeValue::query()->whereIn('id', $attributeValueIds)->get(['id', 'attribute_id']);
        if ($values->count() !== count($attributeValueIds) || $values->pluck('attribute_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['attribute_value_ids' => ['Choose only one value from each attribute, and use valid attribute values.']]);
        }

        $candidate = $values->pluck('id')->sort()->values()->all();
        $duplicate = $product->variants()
            ->when($ignoreVariantId, fn ($query) => $query->whereKeyNot($ignoreVariantId))
            ->with('attributeValues:id')->get()
            ->contains(fn (ProductVariant $variant) => $variant->attributeValues->pluck('id')->sort()->values()->all() === $candidate);

        if ($duplicate) {
            throw ValidationException::withMessages(['attribute_value_ids' => ['This attribute combination already exists for this product.']]);
        }
    }

    /**
     * Stores an uploaded image on the `media` disk (config/filesystems.php
     * — local today, S3-ready later with no code change) and links it to
     * the product or a specific variant.
     */
    public function addImage(Product $product, \Illuminate\Http\UploadedFile $file, ?ProductVariant $variant, bool $isThumbnail, User $actor, ?string $altText = null, ?string $title = null): ProductImage
    {
        $path = $this->storeOptimizedImage($product, $file);

        try {
            $image = ProductImage::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'path' => $path,
                'alt_text' => $altText,
                'title' => $title,
                'is_thumbnail' => $isThumbnail,
                'sort_order' => $product->images()->count(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('media')->delete($path);
            throw $exception;
        }

        $this->auditLogger->log(
            actor: $actor, action: 'image_added', module: 'Catalog',
            entityType: 'Product', entityId: (string) $product->id,
            newValue: ['path' => $path],
        );

        return $image;
    }

    public function replaceImage(Product $product, ProductImage $image, \Illuminate\Http\UploadedFile $file, User $actor, ?string $altText, ?string $title, bool $isThumbnail): ProductImage
    {
        abort_unless($image->product_id === $product->id, 404);
        $oldPath = $image->path;
        $newPath = $this->storeOptimizedImage($product, $file);

        try {
            $image->update([
                'path' => $newPath,
                'alt_text' => $altText,
                'title' => $title,
                'is_thumbnail' => $isThumbnail,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('media')->delete($newPath);
            throw $exception;
        }
        if ($oldPath !== $image->path) {
            Storage::disk('media')->delete($oldPath);
        }
        $this->auditLogger->log(actor: $actor, action: 'image_updated', module: 'Catalog', entityType: 'ProductImage', entityId: (string) $image->id, newValue: ['product_id' => $product->id, 'path' => $image->path]);

        return $image->fresh();
    }

    private function storeOptimizedImage(Product $product, \Illuminate\Http\UploadedFile $file): string
    {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new RuntimeException('The product image could not be read.');
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('The product image format could not be processed.');
        }

        ob_start();
        $encoded = imagewebp($source, null, 90);
        $optimized = ob_get_clean();
        imagedestroy($source);

        if (! $encoded || ! is_string($optimized) || $optimized === '') {
            throw new RuntimeException('The product image could not be optimized.');
        }

        $path = 'products/'.$product->id.'/'.Str::uuid().'.webp';
        if (! Storage::disk('media')->put($path, $optimized)) {
            throw new RuntimeException('The optimized product image could not be stored.');
        }

        return $path;
    }

    public function deleteImage(Product $product, ProductImage $image, User $actor): void
    {
        abort_unless($image->product_id === $product->id, 404);
        Storage::disk('media')->delete($image->path);
        $image->delete();
        $this->auditLogger->log(actor: $actor, action: 'image_deleted', module: 'Catalog', entityType: 'ProductImage', entityId: (string) $image->id, newValue: ['product_id' => $product->id]);
    }

    public function addVideo(Product $product, UploadedFile $file, User $actor, ?string $title = null): ProductVideo
    {
        $sourceHash = hash_file('sha256', $file->getRealPath());
        if (! is_string($sourceHash)) {
            throw new RuntimeException('The product video could not be read.');
        }
        if ($product->videos()->where('source_hash', $sourceHash)->exists()) {
            throw ValidationException::withMessages(['video' => 'This video has already been added to this product.']);
        }

        $mimeType = $file->getMimeType();
        $extension = match ($mimeType) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            default => throw ValidationException::withMessages(['video' => 'Upload an MP4 or WebM video.']),
        };
        $path = 'product-videos/admin/'.$product->id.'/'.Str::uuid().'.'.$extension;
        if (! is_string($file->storeAs(dirname($path), basename($path), 'media'))) {
            throw new RuntimeException('The product video could not be stored.');
        }

        try {
            $video = ProductVideo::create([
                'product_id' => $product->id,
                'source_hash' => $sourceHash,
                'source_url' => url('/storage/media/'.$path),
                'path' => $path,
                'title' => filled($title) ? $title : Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 255, ''),
                'mime_type' => $mimeType,
                'sort_order' => ((int) $product->videos()->max('sort_order')) + 1,
            ]);
        } catch (\Throwable $exception) {
            if (! ProductVideo::query()->where('path', $path)->exists()
                && Storage::disk('media')->exists($path)
                && ! Storage::disk('media')->delete($path)) {
                throw new RuntimeException("Unable to clean up failed video upload {$path}.", previous: $exception);
            }

            throw $exception;
        }

        $this->auditLogger->log(
            actor: $actor, action: 'video_added', module: 'Catalog',
            entityType: 'ProductVideo', entityId: (string) $video->id,
            newValue: ['product_id' => $product->id, 'path' => $path, 'title' => $video->title],
        );

        return $video;
    }

    public function replaceVideo(Product $product, ProductVideo $video, ?UploadedFile $file, User $actor, ?string $title): ProductVideo
    {
        abort_unless($video->product_id === $product->id, 404);

        $oldPath = $video->path;
        $newPath = $oldPath;
        $attributes = ['title' => $title];

        if ($file !== null) {
            $sourceHash = hash_file('sha256', $file->getRealPath());
            if (! is_string($sourceHash)) {
                throw new RuntimeException('The product video could not be read.');
            }
            if ($product->videos()->where('source_hash', $sourceHash)->whereKeyNot($video->id)->exists()) {
                throw ValidationException::withMessages(['video' => 'This video has already been added to this product.']);
            }

            $mimeType = $file->getMimeType();
            $extension = match ($mimeType) {
                'video/mp4' => 'mp4',
                'video/webm' => 'webm',
                default => throw ValidationException::withMessages(['video' => 'Upload an MP4 or WebM video.']),
            };
            $newPath = 'product-videos/admin/'.$product->id.'/'.Str::uuid().'.'.$extension;
            if (! is_string($file->storeAs(dirname($newPath), basename($newPath), 'media'))) {
                throw new RuntimeException('The replacement product video could not be stored.');
            }

            $attributes += [
                'source_hash' => $sourceHash,
                'source_url' => url('/storage/media/'.$newPath),
                'path' => $newPath,
                'mime_type' => $mimeType,
            ];
        }

        try {
            $video->update($attributes);
        } catch (\Throwable $exception) {
            if ($newPath !== $oldPath && Storage::disk('media')->exists($newPath)
                && ! Storage::disk('media')->delete($newPath)) {
                throw new RuntimeException("Unable to clean up failed video replacement {$newPath}.", previous: $exception);
            }

            throw $exception;
        }

        $this->auditLogger->log(
            actor: $actor, action: 'video_updated', module: 'Catalog',
            entityType: 'ProductVideo', entityId: (string) $video->id,
            newValue: ['product_id' => $product->id, 'path' => $newPath, 'title' => $video->title],
        );

        if ($newPath !== $oldPath
            && ! ProductVideo::query()->where('path', $oldPath)->exists()
            && Storage::disk('media')->exists($oldPath)
            && ! Storage::disk('media')->delete($oldPath)) {
            throw new RuntimeException("Unable to remove replaced product video {$oldPath}.");
        }

        return $video->fresh();
    }

    public function deleteVideo(Product $product, ProductVideo $video, User $actor): void
    {
        abort_unless($video->product_id === $product->id, 404);
        $path = $video->path;

        DB::transaction(function () use ($video, $path): void {
            $video->delete();
            $isShared = ProductVideo::query()->where('path', $path)->exists();
            if (! $isShared && Storage::disk('media')->exists($path)
                && ! Storage::disk('media')->delete($path)) {
                throw new RuntimeException("Unable to remove product video {$path}.");
            }
        });

        $this->auditLogger->log(
            actor: $actor, action: 'video_deleted', module: 'Catalog',
            entityType: 'ProductVideo', entityId: (string) $video->id,
            oldValue: ['product_id' => $product->id, 'path' => $path],
        );
    }

    /**
     * Rule 6 enforcement at the catalog level: a sale price must never
     * exceed the regular/variant price. This doesn't replace the
     * server-side checkout recalculation (Phase 9) — it just stops
     * obviously-broken data from ever being saved.
     */
    private function assertPricingIsSane(string|float $regularPrice, string|float|null $salePrice): void
    {
        if ($salePrice === null) {
            return;
        }

        if (bccomp((string) $salePrice, (string) $regularPrice, 2) > 0) {
            throw ValidationException::withMessages([
                'sale_price' => ['Sale price cannot be greater than the regular price.'],
            ]);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use JsonException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplaceCatalogFromExport extends Command
{
    protected $signature = 'catalog:replace-export {directory : Catalog export directory} {--force : Allow destructive replacement in production}';

    protected $description = 'Replace the catalog with a catalog-export directory, including product media';

    public function handle(): int
    {
        $directory = rtrim((string) $this->argument('directory'), DIRECTORY_SEPARATOR);
        $manifestPath = $directory.'/manifest.json';
        if (! is_file($manifestPath)) {
            $this->error("Catalog manifest not found: {$manifestPath}");

            return self::FAILURE;
        }

        try {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Catalog manifest is not valid JSON: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! is_array($manifest) || ($manifest['format'] ?? null) !== 'ecommerce-core-catalog') {
            $this->error('Unsupported catalog export format.');

            return self::FAILURE;
        }

        $validationErrors = $this->validateManifest($manifest, $directory);
        if ($validationErrors !== []) {
            $this->error('Catalog export validation failed:');
            foreach ($validationErrors as $error) {
                $this->line(' - '.$error);
            }

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Catalog replacement is destructive in production. Re-run with --force after verifying the export and backup.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($manifest, $directory): void {
            $this->clearCatalog();
            Storage::disk('media')->deleteDirectory('products');
            Storage::disk('media')->deleteDirectory('categories');

            $categories = $this->importCategories($manifest['categories'] ?? []);
            $this->importProducts($manifest['products'] ?? [], $categories, $directory);
        });

        $this->info('Catalog replacement completed.');

        return self::SUCCESS;
    }

    /**
     * Validate all references and source files before deleting the current
     * catalog. Database transactions cannot restore deleted media files.
     *
     * @return list<string>
     */
    private function validateManifest(array $manifest, string $directory): array
    {
        $errors = [];
        $categories = $manifest['categories'] ?? null;
        $products = $manifest['products'] ?? null;

        if (! is_array($categories) || ! is_array($products)) {
            return ['The manifest must contain categories and products arrays.'];
        }

        $categorySlugs = [];
        foreach ($categories as $index => $data) {
            if (! is_array($data) || trim((string) ($data['slug'] ?? '')) === '') {
                $errors[] = "Category #{$index} is missing a slug.";
                continue;
            }

            $slug = (string) $data['slug'];
            if (isset($categorySlugs[$slug])) {
                $errors[] = "Category slug '{$slug}' is duplicated.";
            }
            $categorySlugs[$slug] = true;

            $parentSlug = trim((string) ($data['parent_slug'] ?? ''));
            if ($parentSlug !== '' && ! isset($categorySlugs[$parentSlug])
                && ! collect($categories)->contains(fn ($category) => is_array($category) && ($category['slug'] ?? null) === $parentSlug)) {
                $errors[] = "Category '{$slug}' references missing parent '{$parentSlug}'.";
            }
        }

        foreach ($products as $index => $data) {
            if (! is_array($data)) {
                $errors[] = "Product #{$index} is not an object.";
                continue;
            }

            $sku = trim((string) ($data['sku'] ?? ''));
            $categorySlug = trim((string) ($data['category_slug'] ?? ''));
            if ($sku === '') {
                $errors[] = "Product #{$index} is missing a SKU.";
            }
            if ($categorySlug === '' || ! isset($categorySlugs[$categorySlug])) {
                $errors[] = "Product '{$sku}' references missing category '{$categorySlug}'.";
            }

            $variants = $data['variants'] ?? [];
            if (! is_array($variants)) {
                $errors[] = "Product '{$sku}' has an invalid variants list.";
                $variants = [];
            }
            $variantSkus = [];
            foreach ($variants as $variantIndex => $variant) {
                $variantSku = is_array($variant) ? trim((string) ($variant['sku'] ?? '')) : '';
                if ($variantSku === '') {
                    $errors[] = "Product '{$sku}' variant #{$variantIndex} is missing a SKU.";
                    continue;
                }
                if (isset($variantSkus[$variantSku])) {
                    $errors[] = "Product '{$sku}' has duplicate variant SKU '{$variantSku}'.";
                }
                $variantSkus[$variantSku] = true;
            }

            $images = $data['images'] ?? [];
            if (! is_array($images)) {
                $errors[] = "Product '{$sku}' has an invalid images list.";
                continue;
            }
            foreach ($images as $imageIndex => $image) {
                if (! is_array($image)) {
                    $errors[] = "Product '{$sku}' image #{$imageIndex} is not an object.";
                    continue;
                }

                $archivePath = (string) ($image['archive_path'] ?? '');
                if (! str_starts_with($archivePath, 'media/')
                    || str_contains($archivePath, '..')
                    || str_contains($archivePath, '\\')) {
                    $errors[] = "Product '{$sku}' image #{$imageIndex} has an unsafe archive path.";
                    continue;
                }
                if (! is_file($directory.'/'.$archivePath)) {
                    $errors[] = "Product '{$sku}' image source '{$archivePath}' does not exist.";
                }

                $variantSku = trim((string) ($image['variant_sku'] ?? ''));
                if ($variantSku !== '' && ! isset($variantSkus[$variantSku])) {
                    $errors[] = "Product '{$sku}' image references missing variant '{$variantSku}'.";
                }
            }
        }

        return $errors;
    }

    private function clearCatalog(): void
    {
        foreach ([
            'cart_items', 'wishlist_items', 'compare_items', 'coupon_products',
            'coupon_categories', 'entrepreneur_products', 'product_variant_attribute_value',
            'product_images', 'product_reviews', 'stock_movements', 'inventories',
            'product_variants', 'product_tag',
        ] as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        Product::withTrashed()->forceDelete();
        Category::withTrashed()->forceDelete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, Category>
     */
    private function importCategories(array $rows): array
    {
        $categories = [];
        foreach ($rows as $data) {
            $category = new Category;
            $category->fill(collect($data)->except(['parent_slug', 'image_path', 'banner_path', 'icon_path'])->all());
            $category->uuid = (string) Str::uuid();
            $category->parent_id = null;
            $category->save();
            $categories[$category->slug] = $category;
        }

        foreach ($rows as $data) {
            if (! empty($data['parent_slug']) && isset($categories[$data['parent_slug']])) {
                $categories[$data['slug']]->update(['parent_id' => $categories[$data['parent_slug']]->id]);
            }
        }

        return $categories;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, Category>  $categories
     */
    private function importProducts(array $rows, array $categories, string $directory): void
    {
        foreach ($rows as $data) {
            $category = $categories[$data['category_slug'] ?? ''] ?? null;
            if (! $category || empty($data['sku'])) {
                continue;
            }

            $product = new Product;
            $product->fill(collect($data)->except(['category_slug', 'variants', 'images'])->all());
            $product->uuid = (string) Str::uuid();
            $product->category_id = $category->id;
            $product->save();

            $variants = [];
            foreach ($data['variants'] ?? [] as $variantData) {
                $variant = $product->variants()->create(collect($variantData)->except(['attribute_values'])->all());
                $variants[$variant->sku] = $variant;
                $attributeValueIds = [];
                foreach ($variantData['attribute_values'] ?? [] as $attributeData) {
                    $attribute = Attribute::firstOrCreate(
                        ['slug' => $attributeData['attribute']],
                        ['name' => Str::headline($attributeData['attribute'])],
                    );
                    $value = AttributeValue::firstOrCreate(
                        ['attribute_id' => $attribute->id, 'value' => $attributeData['value']],
                        ['slug' => Str::slug($attributeData['value'])],
                    );
                    $attributeValueIds[] = $value->id;
                }
                $variant->attributeValues()->sync($attributeValueIds);
            }

            foreach ($data['images'] ?? [] as $imageData) {
                $archivePath = (string) ($imageData['archive_path'] ?? '');
                if (! str_starts_with($archivePath, 'media/') || str_contains($archivePath, '..')) {
                    continue;
                }

                $sourcePath = $directory.'/'.$archivePath;
                if (! is_file($sourcePath)) {
                    continue;
                }

                $path = 'products/'.$product->id.'/'.basename($archivePath);
                Storage::disk('media')->put($path, (string) file_get_contents($sourcePath));
                ProductImage::create([
                    'product_id' => $product->id,
                    'product_variant_id' => isset($variants[$imageData['variant_sku'] ?? ''])
                        ? $variants[$imageData['variant_sku']]->id
                        : null,
                    'path' => $path,
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'title' => $imageData['title'] ?? null,
                    'is_thumbnail' => (bool) ($imageData['is_thumbnail'] ?? false),
                    'sort_order' => (int) ($imageData['sort_order'] ?? 0),
                ]);
            }
        }
    }
}

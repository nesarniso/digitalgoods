<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DefaultCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $snapshotPath = database_path('seeders/data/default-catalog.json');
        if (! is_file($snapshotPath)) {
            throw new RuntimeException('Default catalog snapshot is missing: '.$snapshotPath);
        }

        $catalog = json_decode((string) file_get_contents($snapshotPath), true, 512, JSON_THROW_ON_ERROR);
        if (($catalog['format'] ?? null) !== 'ecommerce-core-default-catalog'
            || (int) ($catalog['version'] ?? 0) !== 1) {
            throw new RuntimeException('Default catalog snapshot has an unsupported format or version.');
        }

        DB::transaction(function () use ($catalog): void {
            $categories = $this->seedCategories($catalog['categories'] ?? []);
            $brands = $this->seedBrands($catalog['brands'] ?? []);
            $this->seedProducts($catalog['products'] ?? [], $categories, $brands);
        });
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(array $records): array
    {
        $categories = [];

        foreach ($records as $record) {
            $category = Category::withTrashed()->firstOrNew(['slug' => $record['slug']]);
            $category->fill(collect($record)->except('parent_slug')->all());
            $category->parent_id = null;
            $category->deleted_at = null;
            $category->save();
            $categories[$category->slug] = $category;
        }

        foreach ($records as $record) {
            if (isset($categories[$record['slug']])) {
                $categories[$record['slug']]->update([
                    'parent_id' => $categories[$record['parent_slug'] ?? '']->id ?? null,
                ]);
            }
        }

        return $categories;
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(array $records): array
    {
        $brands = [];

        foreach ($records as $record) {
            $brand = Brand::withTrashed()->firstOrNew(['slug' => $record['slug']]);
            $brand->fill($record);
            $brand->deleted_at = null;
            $brand->save();
            $brands[$brand->slug] = $brand;
        }

        return $brands;
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  array<string, Brand>  $brands
     */
    private function seedProducts(array $records, array $categories, array $brands): void
    {
        $disk = Storage::disk('media');

        foreach ($records as $record) {
            $category = $categories[$record['category_slug'] ?? ''] ?? null;
            if (! $category) {
                throw new RuntimeException('Default product references a missing category: '.($record['sku'] ?? 'unknown SKU'));
            }

            $product = Product::withTrashed()->firstOrNew(['sku' => $record['sku']]);
            $product->fill(collect($record)->except([
                'category_slug', 'brand_slug', 'variants', 'images',
            ])->all());
            $product->category_id = $category->id;
            $product->brand_id = $brands[$record['brand_slug'] ?? '']->id ?? null;
            $product->deleted_at = null;
            $product->save();

            $variants = $this->seedVariants($product, $record['variants'] ?? []);

            foreach ($record['images'] ?? [] as $imageRecord) {
                $path = (string) ($imageRecord['path'] ?? '');
                if ($path === '' || ! $disk->exists($path)) {
                    throw new RuntimeException("Default product image is missing: {$path}");
                }

                $variantId = $variants[$imageRecord['variant_sku'] ?? '']->id ?? null;
                ProductImage::query()->updateOrCreate(
                    ['product_id' => $product->id, 'path' => $path],
                    [
                        'product_variant_id' => $variantId,
                        'alt_text' => $imageRecord['alt_text'] ?? null,
                        'title' => $imageRecord['title'] ?? null,
                        'is_thumbnail' => (bool) ($imageRecord['is_thumbnail'] ?? false),
                        'sort_order' => (int) ($imageRecord['sort_order'] ?? 0),
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, ProductVariant>
     */
    private function seedVariants(Product $product, array $records): array
    {
        $variants = [];

        foreach ($records as $record) {
            $variant = ProductVariant::withTrashed()->firstOrNew(['sku' => $record['sku']]);
            $variant->fill(collect($record)->except(['attributes'])->all());
            $variant->product_id = $product->id;
            $variant->deleted_at = null;
            $variant->save();

            $attributeValueIds = [];
            foreach ($record['attributes'] ?? [] as $attributeRecord) {
                $attribute = Attribute::query()->firstOrCreate(
                    ['slug' => $attributeRecord['attribute_slug']],
                    ['name' => $attributeRecord['attribute_name']],
                );
                $value = AttributeValue::query()->firstOrCreate(
                    [
                        'attribute_id' => $attribute->id,
                        'value' => $attributeRecord['value'],
                    ],
                    ['slug' => $attributeRecord['value_slug']],
                );
                $attributeValueIds[] = $value->id;
            }
            $variant->attributeValues()->sync($attributeValueIds);
            $variants[$variant->sku] = $variant;
        }

        return $variants;
    }
}

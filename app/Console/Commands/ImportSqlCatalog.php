<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportSqlCatalog extends Command
{
    protected $signature = 'catalog:import-sql {file : MySQL dump file}';

    protected $description = 'Merge categories and products from a MySQL dump without replacing the current database';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (! is_file($file)) {
            $this->error("Dump file not found: {$file}");

            return self::FAILURE;
        }

        try {
            $this->createTemporaryTables();
            $dump = (string) file_get_contents($file);
            $this->loadInsert($dump, 'categories', '__catalog_import_categories');
            $this->loadInsert($dump, 'products', '__catalog_import_products');
            $this->loadInsert($dump, 'product_images', '__catalog_import_product_images');
            $source = DB::connection();

            $categoryMap = $this->importCategories($source);
            [$productMap, $productCount] = $this->importProducts($source, $categoryMap);
            $imageCount = $this->importAvailableImages($source, $productMap);

            $this->info("Imported/updated {$categoryMap['count']} categories and {$productCount} products.");
            $this->info("Imported {$imageCount} media records whose files already exist.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function createTemporaryTables(): void
    {
        DB::statement('CREATE TEMPORARY TABLE __catalog_import_categories (
            id BIGINT UNSIGNED, uuid CHAR(36), parent_id BIGINT UNSIGNED NULL, name VARCHAR(255),
            slug VARCHAR(255), description TEXT NULL, image_path VARCHAR(255) NULL,
            banner_path VARCHAR(255) NULL, icon_path VARCHAR(255) NULL, sort_order INT UNSIGNED,
            is_active TINYINT, is_featured TINYINT, meta_title VARCHAR(255) NULL,
            meta_description VARCHAR(255) NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
            deleted_at TIMESTAMP NULL
        )');
        DB::statement('CREATE TEMPORARY TABLE __catalog_import_products (
            id BIGINT UNSIGNED, uuid CHAR(36), vendor_id BIGINT UNSIGNED NULL, category_id BIGINT UNSIGNED,
            brand_id BIGINT UNSIGNED NULL, name VARCHAR(255), slug VARCHAR(255), sku VARCHAR(255),
            barcode VARCHAR(255) NULL, product_type VARCHAR(30), unit VARCHAR(255) NULL, description TEXT NULL,
            short_description TEXT NULL, purchase_price DECIMAL(12,2) NULL, regular_price DECIMAL(12,2),
            sale_price DECIMAL(12,2) NULL, tax_rate DECIMAL(5,2), weight DECIMAL(10,3) NULL,
            length DECIMAL(10,3) NULL, width DECIMAL(10,3) NULL, height DECIMAL(10,3) NULL,
            minimum_order_quantity INT UNSIGNED, maximum_order_quantity INT UNSIGNED NULL,
            status VARCHAR(30), featured TINYINT, published_at TIMESTAMP NULL, meta_title VARCHAR(255) NULL,
            meta_description VARCHAR(255) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL
        )');
        DB::statement('CREATE TEMPORARY TABLE __catalog_import_product_images (
            id BIGINT UNSIGNED, product_id BIGINT UNSIGNED, product_variant_id BIGINT UNSIGNED NULL,
            path VARCHAR(255), alt_text VARCHAR(255) NULL, title VARCHAR(255) NULL,
            is_thumbnail TINYINT, sort_order INT UNSIGNED, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
        )');
    }

    private function loadInsert(string $dump, string $table, string $temporaryTable): void
    {
        if (preg_match('/INSERT INTO `'.preg_quote($table, '/').'` VALUES .*?;/s', $dump, $match)) {
            DB::unprepared(str_replace("INSERT INTO `{$table}`", "INSERT INTO {$temporaryTable}", $match[0]));
        }
    }

    /**
     * @return array{ids: array<int, int>, count: int}
     */
    private function importCategories($source): array
    {
        $ids = [];
        $rows = $source->table('__catalog_import_categories')->orderBy('id')->get();

        foreach ($rows as $row) {
            $category = Category::withTrashed()->firstOrNew(['slug' => $row->slug]);
            $category->uuid ??= (string) $row->uuid;
            $category->name = $row->name;
            $category->description = $row->description;
            $category->image_path = $row->image_path;
            $category->banner_path = $row->banner_path;
            $category->icon_path = $row->icon_path;
            $category->sort_order = $row->sort_order;
            $category->is_active = $row->is_active;
            $category->is_featured = $row->is_featured;
            $category->meta_title = $row->meta_title;
            $category->meta_description = $row->meta_description;
            $category->deleted_at = null;
            $category->save();
            $ids[(int) $row->id] = (int) $category->id;
        }

        foreach ($rows as $row) {
            if (isset($ids[(int) $row->id])) {
                Category::whereKey($ids[(int) $row->id])->update([
                    'parent_id' => $ids[(int) $row->parent_id] ?? null,
                ]);
            }
        }

        return ['ids' => $ids, 'count' => count($ids)];
    }

    /**
     * @param  array{ids: array<int, int>, count: int}  $categoryMap
     * @return array{0: array<int, int>, 1: int}
     */
    private function importProducts($source, array $categoryMap): array
    {
        $ids = [];
        $count = 0;

        foreach ($source->table('__catalog_import_products')->orderBy('id')->get() as $row) {
            $categoryId = $categoryMap['ids'][(int) $row->category_id] ?? null;
            if (! $categoryId || blank($row->sku)) {
                continue;
            }

            $product = Product::withTrashed()->firstOrNew(['sku' => $row->sku]);
            if (! $product->exists) {
                $product->uuid = (string) $row->uuid;
            }
            $product->fill([
                'category_id' => $categoryId,
                'name' => $row->name,
                'slug' => $row->slug,
                'barcode' => $row->barcode,
                'product_type' => $row->product_type,
                'unit' => $row->unit,
                'description' => $row->description,
                'short_description' => $row->short_description,
                'purchase_price' => $row->purchase_price,
                'regular_price' => $row->regular_price,
                'sale_price' => $row->sale_price,
                'tax_rate' => $row->tax_rate,
                'weight' => $row->weight,
                'length' => $row->length,
                'width' => $row->width,
                'height' => $row->height,
                'minimum_order_quantity' => $row->minimum_order_quantity,
                'maximum_order_quantity' => $row->maximum_order_quantity,
                'status' => $row->status,
                'featured' => $row->featured,
                'published_at' => $row->published_at,
                'meta_title' => $row->meta_title,
                'meta_description' => $row->meta_description,
            ]);
            $product->deleted_at = null;
            $product->save();
            $ids[(int) $row->id] = (int) $product->id;
            $count++;
        }

        return [$ids, $count];
    }

    private function importAvailableImages($source, array $productMap): int
    {
        $count = 0;
        foreach ($source->table('__catalog_import_product_images')->orderBy('id')->get() as $row) {
            $productId = $productMap[(int) $row->product_id] ?? null;
            if (! $productId || ! Storage::disk('media')->exists($row->path)) {
                continue;
            }

            DB::table('product_images')->updateOrInsert(
                ['product_id' => $productId, 'path' => $row->path],
                [
                    'product_variant_id' => null,
                    'alt_text' => $row->alt_text,
                    'title' => $row->title,
                    'is_thumbnail' => $row->is_thumbnail,
                    'sort_order' => $row->sort_order,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ],
            );
            $count++;
        }

        return $count;
    }
}

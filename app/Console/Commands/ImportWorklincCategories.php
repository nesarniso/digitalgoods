<?php

namespace App\Console\Commands;

use App\Domain\Catalog\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ImportWorklincCategories extends Command
{
    protected $signature = 'worklinc:import-categories';

    protected $description = 'Replace the local catalog categories with WorkLinc public product categories';

    private const CATEGORY_ORDER = [
        'reels-bundle' => 10,
        'digital-products' => 20,
        'graphics-pack' => 30,
        'funny' => 40,
        'devotional' => 50,
        'editing-assets' => 60,
        'ai-asmr' => 70,
        'horror' => 80,
        'romantic' => 90,
        'health-fitness' => 100,
        'posters-bundle' => 110,
        'festival' => 120,
        'ai-prompts' => 130,
        'food' => 140,
        'story' => 150,
        'videos-bundle' => 160,
    ];

    public function handle(): int
    {
        $this->info('Reading WorkLinc product categories...');
        $sourceCategories = $this->fetchCategories();

        $categoriesBySourceId = [];
        $slugsBySourceId = [];
        foreach ($sourceCategories as $sourceCategory) {
            $sourceId = (int) $sourceCategory['id'];
            $slug = Str::slug((string) $sourceCategory['slug']);
            $name = trim(html_entity_decode(strip_tags((string) $sourceCategory['name']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($sourceId < 1 || $slug === '' || $name === '') {
                throw new RuntimeException('WorkLinc returned a category with a missing ID, slug, or name.');
            }

            $categoriesBySourceId[$sourceId] = [
                'name' => $name,
                'slug' => $slug,
                'parent' => (int) ($sourceCategory['parent'] ?? 0),
            ];
            $slugsBySourceId[$sourceId] = $slug;
        }

        if (! in_array('digital-products', $slugsBySourceId, true)) {
            throw new RuntimeException('WorkLinc category response did not contain the required Digital Products category.');
        }

        foreach ($categoriesBySourceId as $sourceId => $sourceCategory) {
            if ($sourceCategory['parent'] !== 0 && ! isset($categoriesBySourceId[$sourceCategory['parent']])) {
                throw new RuntimeException("WorkLinc category {$sourceCategory['slug']} references a missing parent.");
            }
        }

        $result = DB::transaction(function () use ($categoriesBySourceId, $slugsBySourceId): array {
            $categories = [];

            foreach ($categoriesBySourceId as $sourceId => $sourceCategory) {
                $category = Category::withTrashed()->firstOrNew(['slug' => $sourceCategory['slug']]);
                if (! $category->exists) {
                    $category->uuid = (string) Str::uuid();
                }

                $category->fill([
                    'parent_id' => null,
                    'name' => $sourceCategory['name'],
                    'description' => null,
                    'image_path' => null,
                    'banner_path' => null,
                    'icon_path' => null,
                    'sort_order' => self::CATEGORY_ORDER[$sourceCategory['slug']] ?? (1000 + $sourceId),
                    'is_active' => true,
                    'is_featured' => $sourceCategory['slug'] === 'digital-products',
                    'meta_title' => $sourceCategory['name'],
                    'meta_description' => null,
                ]);
                $category->deleted_at = null;
                $category->save();
                $categories[$sourceId] = $category;
            }

            foreach ($categoriesBySourceId as $sourceId => $sourceCategory) {
                $categories[$sourceId]->parent_id = $categories[$sourceCategory['parent']]->id ?? null;
                $categories[$sourceId]->save();
            }

            $digitalProductsId = $categories[array_search('digital-products', $slugsBySourceId, true)]->id;
            $importedSlugs = array_values($slugsBySourceId);
            $obsoleteCategoryIds = Category::withTrashed()
                ->whereNotIn('slug', $importedSlugs)
                ->pluck('id');

            $reassignedProducts = 0;
            $mappedCoupons = 0;
            if ($obsoleteCategoryIds->isNotEmpty()) {
                $reassignedProducts = DB::table('products')
                    ->whereIn('category_id', $obsoleteCategoryIds)
                    ->update(['category_id' => $digitalProductsId]);

                $couponIds = DB::table('coupon_categories')
                    ->whereIn('category_id', $obsoleteCategoryIds)
                    ->distinct()
                    ->pluck('coupon_id');

                DB::table('coupon_categories')->whereIn('category_id', $obsoleteCategoryIds)->delete();
                foreach ($couponIds as $couponId) {
                    DB::table('coupon_categories')->insertOrIgnore([
                        'coupon_id' => $couponId,
                        'category_id' => $digitalProductsId,
                    ]);
                }
                $mappedCoupons = $couponIds->count();

                Category::withTrashed()->whereIn('id', $obsoleteCategoryIds)->forceDelete();
            }

            return [
                'category_count' => count($categories),
                'reassigned_products' => $reassignedProducts,
                'mapped_coupons' => $mappedCoupons,
            ];
        });

        $this->info("Imported {$result['category_count']} categories; reassigned {$result['reassigned_products']} products.");
        if ($result['mapped_coupons'] > 0) {
            $this->warn("Mapped {$result['mapped_coupons']} category-scoped coupons to Digital Products.");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{id: int|string, name: string, slug: string, parent?: int|string}>
     */
    private function fetchCategories(): array
    {
        $endpoint = 'https://shop.worklinc.com/wp-json/wc/store/v1/products/categories';
        $categories = [];
        $page = 1;
        $lastPage = 1;

        do {
            $response = Http::acceptJson()
                ->timeout(20)
                ->get($endpoint, ['per_page' => 100, 'page' => $page])
                ->throw();
            $payload = $response->json();

            if (! is_array($payload) || ! array_is_list($payload)) {
                throw new RuntimeException("WorkLinc returned an invalid category list on page {$page}.");
            }

            foreach ($payload as $category) {
                if (! is_array($category)) {
                    throw new RuntimeException("WorkLinc returned an invalid category record on page {$page}.");
                }
                $categories[] = $category;
            }

            $lastPage = max($page, (int) $response->header('X-WP-TotalPages', $page));
            $page++;
        } while ($page <= $lastPage);

        if ($categories === []) {
            throw new RuntimeException('WorkLinc returned no product categories.');
        }

        return $categories;
    }
}

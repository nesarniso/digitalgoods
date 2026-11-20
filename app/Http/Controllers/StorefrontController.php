<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Analytics\Services\AnalyticsService;
use App\Domain\Commerce\Models\WishlistItem;
use App\Domain\Catalog\Models\ProductReview;
use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Settings\Services\FeatureFlagService;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorefrontController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly AnalyticsService $analytics,
        private readonly FeatureFlagService $featureFlags,
    ) {}

    public function home()
    {
        $limitedOfferEnabled = (bool) filter_var($this->settings->get('homepage.limited_offer_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $limitedOfferEndsAt = filled($this->settings->get('homepage.limited_offer_ends_at'))
            ? Carbon::parse($this->settings->get('homepage.limited_offer_ends_at'))
            : null;
        $limitedOfferProductIds = json_decode((string) $this->settings->get('homepage.limited_offer_product_ids', '[]'), true);
        if (! is_array($limitedOfferProductIds)) {
            Log::warning('Invalid limited-offer product setting.', ['value' => $this->settings->get('homepage.limited_offer_product_ids')]);
            $limitedOfferProductIds = [];
        }
        $limitedOfferProductIds = array_values(array_filter(array_map('intval', $limitedOfferProductIds)));
        $limitedOfferProducts = collect();
        if ($limitedOfferEnabled && $limitedOfferEndsAt?->isFuture() && $limitedOfferProductIds !== []) {
            $products = $this->publishedProducts(false)
                ->whereIn('id', $limitedOfferProductIds)
                ->get()
                ->keyBy('id');
            $limitedOfferProducts = collect($limitedOfferProductIds)
                ->map(fn (int $id) => $products->get($id))
                ->filter()
                ->values();
        }

        $homepage = [
            'slogan' => $this->settings->get('system.slogan', 'আপনার উদ্যোগ, আমাদের প্ল্যাটফর্ম'),
            'hero_enabled' => (bool) filter_var($this->settings->get('homepage.hero_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'hero_title' => $this->settings->get('homepage.hero_title', 'Everyday products, delivered simply.'),
            'hero_subtitle' => $this->settings->get('homepage.hero_subtitle', 'Discover thoughtfully selected products, transparent pricing and dependable delivery from one trusted store.'),
            'hero_button_label' => $this->settings->get('homepage.hero_button_label', 'Explore the shop'),
            'faq_eyebrow' => $this->settings->get('homepage.faq_eyebrow', 'FAQ'),
            'faq_title' => $this->settings->get('homepage.faq_title', 'সাধারণ প্রশ্ন'),
            'faq_items' => $this->homepageTextGroup(
                ['কীভাবে sale track হবে?', 'Profit কখন পাওয়া যাবে?'],
                [
                    'Order confirmation এবং account history থেকে আপনার order status ও delivery updates দেখতে পারবেন।',
                    'Successful order এবং return verification-এর পর eligible refund বা adjustment process করা হবে।',
                ],
                'faq_item'
            ),
            'slider_image_1' => $this->mediaUrl($this->settings->get('homepage.slider_image_1')),
            'slider_image_2' => $this->mediaUrl($this->settings->get('homepage.slider_image_2')),
            'slider_image_3' => $this->mediaUrl($this->settings->get('homepage.slider_image_3')),
            'category_section_enabled' => (bool) filter_var($this->settings->get('homepage.category_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'category_limit' => max(4, (int) $this->settings->get('homepage.category_limit', 8)),
            'featured_section_enabled' => (bool) filter_var($this->settings->get('homepage.featured_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'featured_limit' => max(10, (int) $this->settings->get('homepage.featured_limit', 10)),
        ];

        $recommendedProduct = $this->publishedProducts()
            ->where('featured', true)
            ->latest('published_at')
            ->first()
            ?? $this->publishedProducts()->latest('published_at')->first();

        $visitorId = $this->analytics->track(request(), 'page_view');

        return response()
            ->view('storefront.home', [
            'homeSettings' => $homepage,
            'limitedOfferEndsAt' => $limitedOfferEndsAt,
            'limitedOfferProducts' => $limitedOfferProducts,
            'homeStats' => [
                'products' => $this->publishedProducts()->count(),
                'entrepreneurs' => EntrepreneurProfile::query()->where('status', 'active')->count(),
                'orders' => max(24560, Order::query()->whereIn('status', ['delivered', 'completed'])->count()),
                'categories' => Category::query()->where('is_active', true)->count(),
            ],
            'categories' => Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'categoryTiles' => Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(12)
                ->get()
                ->pad(12, null),
            'featuredCategories' => Category::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->whereHas('products', fn ($query) => $query->where('status', 'published'))
                ->with(['products' => fn ($query) => $query
                    ->where('status', 'published')
                    ->with([
                        'category',
                        'images',
                        'variants.attributeValues.attribute',
                        'reviews' => fn ($reviewQuery) => $reviewQuery->where('status', 'approved'),
                    ])
                    ->latest('published_at')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'latestProducts' => $this->publishedProducts()
                ->latest('published_at')
                ->limit($homepage['featured_limit'])
                ->get(),
            'premiumBundlePlans' => $this->publishedProducts(false)
                ->whereIn('slug', ['all-bundles', 'all-bundles-reseller-rights'])
                ->get()
                ->keyBy('slug'),
            'recommendedProduct' => $recommendedProduct,
            'mostSellingProducts' => $this->mostSellingProducts(),
            ])
            ->cookie(AnalyticsService::COOKIE, $visitorId, 60 * 24 * 365, '/', null, request()->isSecure(), true, false, 'lax');
    }

    private function homepageTextGroup(array $defaultTitles, array $defaultTexts, string $prefix): array
    {
        $count = (int) $this->settings->get("homepage.{$prefix}_count", count($defaultTitles));
        $count = max(0, min($count, 50));

        if ($count === 0) {
            return [];
        }

        return array_map(fn (int $index) => [
            'title' => $this->settings->get("homepage.{$prefix}_title_{$index}", $defaultTitles[$index] ?? ''),
            'text' => $this->settings->get("homepage.{$prefix}_text_{$index}", $defaultTexts[$index] ?? ''),
        ], range(0, $count - 1));
    }

    private function mostSellingProducts()
    {
        if (! (bool) filter_var($this->settings->get('homepage.most_selling_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            return collect();
        }

        $ids = json_decode((string) $this->settings->get('homepage.most_selling_product_ids', '[]'), true);
        if (! is_array($ids)) {
            Log::warning('Invalid most-selling product setting.', ['value' => $this->settings->get('homepage.most_selling_product_ids')]);
            $ids = [];
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));
        if ($ids === []) {
            return collect();
        }

        $products = $this->publishedProducts()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)->map(fn (int $id) => $products->get($id))->filter()->values();
    }

    public function shop(Request $request)
    {
        $selectedCategory = $request->string('category')->toString();
        $products = $this->publishedProducts()
            ->when($request->filled('q'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhere('sku', 'like', '%'.$request->string('q')->toString().'%');
            }))
            ->when($selectedCategory !== '', fn ($query) => $query->where(function ($query) use ($selectedCategory) {
                $categoryFilter = fn ($category) => $category
                    ->where('slug', $selectedCategory)
                    ->orWhereHas('parent', fn ($parent) => $parent->where('slug', $selectedCategory))
                    ->orWhereHas('parent.parent', fn ($parent) => $parent->where('slug', $selectedCategory));

                $query->whereHas('category', $categoryFilter)
                    ->orWhereHas('categories', $categoryFilter);
            }))
            ->when($request->string('sort')->toString() === 'price_low', fn ($query) => $query->orderBy('sale_price')->orderBy('regular_price'))
            ->when($request->string('sort')->toString() === 'price_high', fn ($query) => $query->orderByDesc('sale_price')->orderByDesc('regular_price'))
            ->when(! in_array($request->string('sort')->toString(), ['price_low', 'price_high'], true), fn ($query) => $query->latest('published_at'))
            ->paginate(12)
            ->withQueryString();

        $visitorId = $this->analytics->track($request, 'page_view');

        return response()
            ->view('storefront.shop', [
            'products' => $products,
            'categories' => Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            ])
            ->cookie(AnalyticsService::COOKIE, $visitorId, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax');
    }

    public function reels(Request $request)
    {
        $categoryFilter = fn ($category) => $category
            ->where('slug', 'reels-bundle')
            ->orWhereHas('parent', fn ($parent) => $parent->where('slug', 'reels-bundle'))
            ->orWhereHas('parent.parent', fn ($parent) => $parent->where('slug', 'reels-bundle'));

        $products = $this->publishedProducts(withReviews: false)
            ->whereHas('videos')
            ->where(fn ($query) => $query
                ->whereHas('category', $categoryFilter)
                ->orWhereHas('categories', $categoryFilter))
            ->with(['images', 'videos'])
            ->latest('published_at')
            ->get();

        $visitorId = $this->analytics->track($request, 'page_view');

        return response()
            ->view('storefront.reels', ['products' => $products])
            ->cookie(AnalyticsService::COOKIE, $visitorId, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax');
    }

    public function product(Request $request, string $slug)
    {
        $ratingFilter = $request->integer('rating');
        abort_if($request->filled('rating') && ($ratingFilter < 1 || $ratingFilter > 5), 404);
        $reviewSort = $request->string('review_sort')->toString();
        if (! in_array($reviewSort, ['newest', 'highest', 'lowest'], true)) {
            $reviewSort = 'newest';
        }

        $product = $this->publishedProducts(withReviews: false)
            ->where('slug', $slug)
            ->with([
                'category',
                'brand',
                'images',
                'videos',
                'variants' => fn ($query) => $query->orderBy('id')->with('attributeValues.attribute'),
            ])
            ->firstOrFail();

        $reviewCounts = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');
        $reviewCounts = collect(range(1, 5))
            ->mapWithKeys(fn (int $rating): array => [$rating => (int) ($reviewCounts[$rating] ?? 0)]);
        $totalReviews = (int) $reviewCounts->sum();
        $averageRating = $totalReviews > 0
            ? $reviewCounts->sum(fn ($count, $rating) => (int) $rating * (int) $count) / $totalReviews
            : null;
        $reviewsQuery = $product->reviews()->reorder()->with('user', 'order.items');
        if ($ratingFilter > 0) {
            $reviewsQuery->where('rating', $ratingFilter);
        }
        match ($reviewSort) {
            'highest' => $reviewsQuery->orderByDesc('rating')->orderByDesc('created_at'),
            'lowest' => $reviewsQuery->orderBy('rating')->orderByDesc('created_at'),
            default => $reviewsQuery->orderByDesc('created_at'),
        };
        $reviews = $reviewsQuery->paginate(8, ['*'], 'reviews_page')->withQueryString();

        $relatedProducts = $this->publishedProducts()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->getKey())
            ->latest('published_at')
            ->limit(4)
            ->get();
        $user = $request->user();
        $entrepreneurCanSell = $user?->hasRole('entrepreneur')
            && $user->entrepreneurProfile?->status === 'active'
            && $product->purchase_price !== null;
        $entrepreneurHasProduct = $entrepreneurCanSell
            && $user->entrepreneurProducts()->where('product_id', $product->id)->where('status', 'active')->exists();
        $shareUrl = $entrepreneurHasProduct
            ? route('entrepreneur.selling-link', [$user->entrepreneurProfile->entrepreneur_code, $product->slug])
            : route('products.show', $product->slug);

        $this->analytics->track($request, 'page_view');
        $visitorId = $this->analytics->track($request, 'product_view', $product);

        return response()
            ->view('storefront.product', [
            'product' => $product,
            'variantAttributes' => $product->variants
                ->flatMap(fn ($variant) => $variant->attributeValues)
                ->groupBy('attribute_id')
                ->map(fn ($values) => $values->unique('id')->sortBy('value')->values()),
            'relatedProducts' => $relatedProducts,
            'productOrderCount' => OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', fn ($query) => $query->whereNotIn('status', ['cancelled', 'failed', 'refunded']))
                ->distinct('order_id')
                ->count('order_id'),
            'productWishlistCount' => WishlistItem::query()->where('product_id', $product->id)->count(),
            'reviews' => $reviews,
            'reviewCounts' => $reviewCounts,
            'totalReviews' => $totalReviews,
            'averageRating' => $averageRating,
            'ratingFilter' => $ratingFilter ?: null,
            'reviewSort' => $reviewSort,
            'reviewOrders' => $request->user()
                ? $request->user()->orders()
                    ->where('status', 'delivered')
                    ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                    ->whereNotIn('id', ProductReview::query()
                        ->where('product_id', $product->id)
                        ->where('user_id', $request->user()->id)
                        ->select('order_id'))
                    ->latest('id')
                    ->get()
                : collect(),
            'entrepreneurCanSell' => $entrepreneurCanSell,
            'entrepreneurHasProduct' => $entrepreneurHasProduct,
            'compareEnabled' => $this->featureFlags->isEnabled('compare'),
            'shareUrl' => $shareUrl,
            ])
            ->cookie(AnalyticsService::COOKIE, $visitorId, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax');
    }

    private function publishedProducts(bool $withReviews = true)
    {
        $query = Product::query()
            ->where('status', 'published')
            ->with([
                'category',
                'brand',
                'images',
                'variants.attributeValues.attribute',
            ]);

        if ($withReviews) {
            $query->with(['reviews']);
        }

        return $query;
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path || ! Storage::disk('media')->exists($path)) {
            return null;
        }

        return asset('storage/media/'.$path);
    }
}

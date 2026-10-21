<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function admin()
    {
        return view('admin.modules.seo', [
            'sitemapUrl' => route('seo.sitemap'),
            'robotsUrl' => route('seo.robots'),
            'storefrontUrl' => route('home'),
        ]);
    }

    public function robots(): Response
    {
        return response("User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /account/\nDisallow: /checkout\nDisallow: /cart\n\nSitemap: ".url('/sitemap.xml')."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'lastmod' => now()->toDateString()],
            ['loc' => route('shop'), 'lastmod' => now()->toDateString()],
        ])->merge(
            Product::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->cursor()
                ->map(fn (Product $product) => ['loc' => route('products.show', $product->slug), 'lastmod' => $product->updated_at?->toDateString()])
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $item) {
            $xml .= '<url><loc>'.e($item['loc']).'</loc><lastmod>'.e($item['lastmod'] ?? now()->toDateString()).'</lastmod></url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}

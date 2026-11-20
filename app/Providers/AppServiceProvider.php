<?php

namespace App\Providers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Category;
use App\Domain\Content\Models\Page;
use App\Policies\ProductPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('orders', function (Request $request): \Illuminate\Cache\RateLimiting\Limit {
            $user = $request->user('sanctum');
            $cartToken = trim((string) $request->header('X-Guest-Cart-Token'));
            $key = $user
                ? 'user:'.$user->getAuthIdentifier()
                : ($cartToken !== '' ? 'cart:'.hash('sha256', $cartToken) : 'ip:'.$request->ip());

            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($key);
        });

        // Fail loudly on lazy-loaded relations outside production so N+1
        // queries are caught in development/CI rather than in a client's
        // production catalog.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Force HTTPS URL generation when running behind a proxy/load
        // balancer in production (checkout/payment pages must be secure).
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Explicit registration: Laravel's policy auto-discovery only
        // guesses App\Policies\{Model}Policy for models under App\Models.
        // Every Domain/* model needs its policy registered here instead.
        Gate::policy(Product::class, ProductPolicy::class);

        // Keep booting when the database is unavailable during maintenance or deployment.
        try {
            $settings = Schema::hasTable('settings') ? app(\App\Domain\Settings\Services\SettingService::class) : null;
        } catch (\Throwable) {
            $settings = null;
        }
        if ($settings) {
            $siteName = trim((string) $settings->get('system.site_name', config('app.name')));
            if ($siteName !== '') {
                Config::set('app.name', $siteName);
            }
            Config::set('mail.default', $settings->get('mail.mailer', config('mail.default')));
            Config::set('mail.mailers.smtp.host', $settings->get('mail.host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', (int) $settings->get('mail.port', config('mail.mailers.smtp.port')));
            Config::set('mail.mailers.smtp.username', $settings->get('mail.username', config('mail.mailers.smtp.username')));
            Config::set('mail.mailers.smtp.password', $settings->get('mail.password', config('mail.mailers.smtp.password')));
            Config::set('mail.mailers.smtp.encryption', $settings->get('mail.encryption', config('mail.mailers.smtp.encryption')));
            $mailEncryption = $settings->get('mail.encryption', config('mail.mailers.smtp.encryption'));
            Config::set('mail.mailers.smtp.scheme', $mailEncryption === 'ssl' ? 'smtps' : 'smtp');
            Config::set('mail.from.address', $settings->get('mail.from_address', config('mail.from.address')));
            Config::set('mail.from.name', $settings->get('mail.from_name', config('mail.from.name')));
        }

        View::composer(['layouts.storefront', 'layouts.admin', 'layouts.entrepreneur'], function ($view): void {
            $settings = app(\App\Domain\Settings\Services\SettingService::class);
            $logo = $settings->get('system.logo');
            $websiteIcon = $settings->get('system.website_icon');

            $view->with('systemSettings', [
                'brand_color' => $settings->get('system.brand_color', '#f97316'),
                'site_name' => $settings->get('system.site_name', config('app.name')),
                'slogan' => $settings->get('system.slogan', 'আপনার উদ্যোগ, আমাদের প্ল্যাটফর্ম'),
                'site_url' => $settings->get('system.site_url', config('app.url')),
                'phone' => $settings->get('system.phone', ''),
                'email' => $settings->get('system.email', ''),
                'whatsapp' => $settings->get('system.whatsapp', ''),
                'address' => $settings->get('system.address', ''),
                'logo_url' => $logo && Storage::disk('media')->exists($logo) ? asset('storage/media/'.$logo) : null,
                'website_icon_url' => $websiteIcon && Storage::disk('media')->exists($websiteIcon) ? asset('storage/media/'.$websiteIcon) : asset('icons/icon.svg'),
            ]);
            $view->with('footerSettings', [
                'enabled' => (bool) filter_var($settings->get('footer.enabled', true), FILTER_VALIDATE_BOOLEAN),
                'description' => $settings->get('footer.description', 'Your trusted destination for quality products, fair prices and dependable delivery.'),
                'copyright' => $settings->get('footer.copyright', 'All rights reserved.'),
                'newsletter_title' => $settings->get('footer.newsletter_title', 'Stay in the loop'),
                'newsletter_text' => $settings->get('footer.newsletter_text', 'Get updates about new products and special offers.'),
                'facebook_url' => $settings->get('footer.facebook_url', ''),
                'instagram_url' => $settings->get('footer.instagram_url', ''),
                'youtube_url' => $settings->get('footer.youtube_url', ''),
                'column_one_title' => $settings->get('footer.column_one_title', 'Quick links'),
                'column_one_links' => self::footerLinks($settings, 'one'),
                'column_two_title' => $settings->get('footer.column_two_title', 'Customer care'),
                'column_two_links' => self::footerLinks($settings, 'two'),
            ]);
            $view->with('footerPages', Page::query()->where('is_published', true)->where('show_in_footer', true)->orderBy('title')->get());
            if ($view->getName() === 'layouts.admin') {
                $view->with('adminContentPages', Page::query()
                    ->whereIn('slug', ['about-us', 'returns-refunds'])
                    ->get()
                    ->keyBy('slug'));
            }
            if ($view->getName() === 'layouts.storefront') {
                $view->with('categories', Category::query()
                    ->where('is_active', true)
                    ->whereNull('parent_id')
                    ->with(['children' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with(['children' => fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')])])
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get());
            }
        });
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private static function footerLinks(\App\Domain\Settings\Services\SettingService $settings, string $column): array
    {
        $links = [];
        foreach (range(1, 3) as $index) {
            $label = trim((string) $settings->get("footer.column_{$column}_link_{$index}_label", ''));
            $url = trim((string) $settings->get("footer.column_{$column}_link_{$index}_url", ''));
            if ($label !== '' && $url !== '') {
                $links[] = ['label' => $label, 'url' => $url];
            }
        }

        if ($links !== []) {
            return $links;
        }

        return $column === 'one'
            ? [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Shop', 'url' => route('shop')],
                ['label' => 'Become an entrepreneur', 'url' => route('entrepreneur.register')],
                ['label' => 'Contact us', 'url' => route('contact')],
            ]
            : [
                ['label' => 'Customer login', 'url' => route('account.login')],
                ['label' => 'Create an account', 'url' => route('account.register')],
                ['label' => 'My cart', 'url' => route('cart')],
                ['label' => 'Support', 'url' => route('contact')],
            ];
    }
}

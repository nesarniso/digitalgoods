<?php

namespace App\Providers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Contracts\SearchServiceInterface;
use App\Domain\Catalog\Services\AttributeService;
use App\Domain\Catalog\Services\BrandService;
use App\Domain\Catalog\Services\CategoryService;
use App\Domain\Catalog\Services\DatabaseSearchService;
use App\Domain\Catalog\Services\ProductService;
use App\Domain\Commerce\Services\CartService;
use App\Domain\Commerce\Services\CheckoutService;
use App\Domain\Commerce\Services\CompareService;
use App\Domain\Commerce\Services\CouponService;
use App\Domain\Commerce\Services\PricingService;
use App\Domain\Commerce\Services\TaxService;
use App\Domain\Commerce\Services\WishlistService;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Payments\Services\PaymentService;
use App\Domain\Shipping\Contracts\CourierGatewayInterface;
use App\Domain\Shipping\Contracts\CourierInterface;
use App\Domain\Shipping\Couriers\ManualCourier;
use App\Domain\Shipping\Services\ShipmentService;
use App\Domain\Shipping\Services\ShippingConfigService;
use App\Domain\Shipping\Services\ShippingService;
use App\Domain\Users\Services\AddressService;
use App\Domain\Inventory\Services\InventoryService;
use App\Domain\Inventory\Services\RestockRequestService;
use App\Domain\Inventory\Services\WarehouseService;
use App\Domain\Settings\Services\FeatureFlagService;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\ServiceProvider;

/**
 * Central place where each Domain/* module's services and contracts get
 * bound into the container. Controllers and other services should type-hint
 * against these bindings rather than `new SomeService()` directly, so
 * gateway/courier/search adapters (Phases 8, 10, etc.) can be swapped via
 * config without touching call sites — see architecture doc sections 7 & 10.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(SettingService::class);
        $this->app->singleton(FeatureFlagService::class);

        $this->app->singleton(CategoryService::class);
        $this->app->singleton(BrandService::class);
        $this->app->singleton(ProductService::class);
        $this->app->singleton(AttributeService::class);

        $this->app->singleton(InventoryService::class);
        $this->app->singleton(WarehouseService::class);
        $this->app->singleton(RestockRequestService::class);

        $this->app->singleton(CartService::class);
        $this->app->singleton(WishlistService::class);
        $this->app->singleton(CompareService::class);
        $this->app->singleton(CouponService::class);
        $this->app->singleton(TaxService::class);
        $this->app->singleton(PricingService::class);
        $this->app->singleton(CheckoutService::class);

        $this->app->singleton(PaymentService::class);

        $this->app->singleton(OrderService::class);

        $this->app->singleton(AddressService::class);
        $this->app->singleton(ShippingService::class);
        $this->app->singleton(ShippingConfigService::class);
        $this->app->singleton(ShipmentService::class);

        $this->app->bind(CourierGatewayInterface::class, function () {
            $driver = config('couriers.default', 'manual');
            $config = config('couriers.drivers.'.$driver, ['class' => ManualCourier::class]);
            $class = $config['class'] ?? ManualCourier::class;

            return class_exists($class) ? $this->app->make($class) : $this->app->make(ManualCourier::class);
        });

        $this->app->bind(CourierInterface::class, CourierGatewayInterface::class);

        // Search driver resolved from config/search.php — 'database' today,
        // swappable to a Meilisearch/Elasticsearch class later without
        // touching anything that calls SearchServiceInterface.
        $this->app->bind(SearchServiceInterface::class, match (config('search.driver', 'database')) {
            default => DatabaseSearchService::class,
        });

        // Later phases add bindings here, e.g.:
        // $this->app->bind(PaymentGatewayInterface::class, fn ($app) =>
        //     $app->make(config('payments.default_gateway')));
    }

    public function boot(): void
    {
        //
    }
}

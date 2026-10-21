<?php

use App\Http\Controllers\Api\V1\Admin\AttributeController;
use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CouponController;
use App\Http\Controllers\Api\V1\Admin\ShippingMethodController;
use App\Http\Controllers\Api\V1\Admin\ShippingZoneController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;
use App\Http\Controllers\Api\V1\Admin\FeatureFlagController;
use App\Http\Controllers\Api\V1\Admin\InventoryController;
use App\Http\Controllers\Api\V1\Admin\PaymentTransactionController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\RestockRequestController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\WarehouseController;
use App\Http\Controllers\Api\V1\CourierWebhookController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\SessionController;
use App\Http\Controllers\Api\V1\Storefront\CartController;
use App\Http\Controllers\Api\V1\Storefront\CatalogController;
use App\Http\Controllers\Api\V1\Storefront\CheckoutController;
use App\Http\Controllers\Api\V1\Storefront\AddressController;
use App\Http\Controllers\Api\V1\Storefront\CompareController;
use App\Http\Controllers\Api\V1\Storefront\OrderController;
use App\Http\Controllers\Api\V1\Storefront\ShippingController;
use App\Http\Controllers\Api\V1\Storefront\WishlistController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
| All API routes are versioned per spec section 70 / instruction #26.
| Cart/checkout endpoints are added starting Phase 6 onward — this file
| currently covers foundation (ping), Phase 2 (auth), Phase 3 (admin
| settings/feature-flags/employees/audit), and Phase 4 (catalog).
*/
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::get('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'pong',
            'data' => ['version' => 'v1'],
            'meta' => (object) [],
        ]);
    })->name('ping');

    // Public storefront catalog — no auth required, matches what a
    // storefront/Flutter customer app hits directly.
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('/categories', [CatalogController::class, 'categories'])->name('categories');
        Route::get('/products', [CatalogController::class, 'products'])->name('products.index');
        Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('products.show');
    });

    // Cart works for guests (X-Guest-Cart-Token header) and authenticated
    // customers alike — no auth:sanctum middleware here on purpose, since
    // that would reject guest requests outright. See CartController.
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'show'])->name('show');
        Route::post('/items', [CartController::class, 'addItem'])->name('items.store');
        Route::put('/items/{item}', [CartController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}', [CartController::class, 'removeItem'])->name('items.destroy');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::post('/checkout-contact', [CartController::class, 'saveCheckoutContact'])->name('checkout-contact');
        Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
        Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    });

    // Shipping-rate lookup works for guests too (same cart resolution as
    // CartController) since a guest can still pick an address at
    // checkout without an account — Phase 9 wires the actual checkout
    // flow, this just answers "what will shipping cost".
    Route::get('/shipping/methods', [ShippingController::class, 'methods'])->name('shipping.methods');

    // Third-party courier webhooks verify the caller's HMAC signature and
    // reject duplicate deliveries. The event payload is idempotent by
    // courier + event_id, so network retries do not trigger duplicate
    // shipment-state mutation.
    Route::post('/couriers/webhooks/{courier}', CourierWebhookController::class)->name('couriers.webhooks');

    // Checkout summary works for guests too, same identity resolution as
    // cart/shipping — "place order" itself doesn't exist until Phase 11
    // (Orders), this only computes the authoritative preview.
    Route::post('/checkout/summary', [CheckoutController::class, 'summary'])->middleware('throttle:60,1')->name('checkout.summary');
    Route::post('/checkout/account-status', [OrderController::class, 'accountStatus'])->middleware('throttle:60,1')->name('checkout.account-status');
    Route::post('/checkout/account-login', [OrderController::class, 'accountLogin'])->middleware('throttle:30,1')->name('checkout.account-login');

    // Placing an order works for guests too (same identity resolution as
    // checkout/cart) — deliberately NOT behind auth:sanctum. Listing/
    // viewing MY orders requires an account; guests use the
    // order-number + email lookup instead.
    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders')->name('orders.store');
    Route::get('/orders/lookup', [OrderController::class, 'lookup'])->middleware('throttle:5,1')->name('orders.lookup');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    // Wishlist and Compare require a real account — unlike cart, these
    // aren't meaningful for an anonymous guest to persist across devices.
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('wishlist')->name('wishlist.')->group(function () {
            Route::get('/', [WishlistController::class, 'index'])->name('index');
            Route::post('/', [WishlistController::class, 'store'])->name('store');
            Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('compare')->name('compare.')->group(function () {
            Route::get('/', [CompareController::class, 'index'])->name('index');
            Route::post('/', [CompareController::class, 'store'])->name('store');
            Route::delete('/{product}', [CompareController::class, 'destroy'])->name('destroy');
        });

        Route::apiResource('addresses', AddressController::class)->except(['show']);
    });

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', RegisterController::class)
            ->middleware('throttle:12,1')
            ->name('register');

        Route::post('/login', LoginController::class)
            ->middleware('throttle:12,1')
            ->name('login');

        Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
            ->middleware('throttle:10,10')
            ->name('password.email');

        Route::post('/reset-password', [PasswordResetController::class, 'reset'])
            ->middleware('throttle:20,10')
            ->name('password.reset');

        Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware('signed', 'throttle:6,1')
            ->name('verification.verify');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', LogoutController::class)->name('logout');

            Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('verification.resend');

            Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
            Route::delete('/sessions/{tokenId}', [SessionController::class, 'destroy'])->name('sessions.destroy');
        });
    });

    Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated user retrieved.',
            'data' => new UserResource($request->user()->load('roles')),
            'meta' => (object) [],
        ]);
    })->name('me');

    // Admin surface — every route here additionally checks a specific
    // permission (or policy, for Product) inside its controller/Form
    // Request, per spec section 5's granular-permission requirement.
    // auth:sanctum only proves identity; the permission check proves
    // authorization.
    Route::prefix('admin')->name('admin.')->middleware('auth:sanctum')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings/{key}', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/feature-flags', [FeatureFlagController::class, 'index'])->name('feature-flags.index');
        Route::post('/feature-flags/{key}/enable', [FeatureFlagController::class, 'enable'])->name('feature-flags.enable');
        Route::post('/feature-flags/{key}/disable', [FeatureFlagController::class, 'disable'])->name('feature-flags.disable');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{employee}/role', [EmployeeController::class, 'updateRole'])->name('employees.role');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::apiResource('categories', CategoryController::class)->except(['show']);
        Route::apiResource('brands', BrandController::class)->except(['show']);

        Route::apiResource('products', ProductController::class);
        Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');

        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::post('/attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');

        Route::apiResource('coupons', CouponController::class)->except(['show']);

        Route::get('/payment-transactions', [PaymentTransactionController::class, 'index'])->name('payment-transactions.index');
        Route::post('/payment-transactions/{transaction}/confirm', [PaymentTransactionController::class, 'confirm'])->name('payment-transactions.confirm');
        Route::post('/payment-transactions/{transaction}/reject', [PaymentTransactionController::class, 'reject'])->name('payment-transactions.reject');
        Route::post('/payment-transactions/{transaction}/refund', [PaymentTransactionController::class, 'refund'])->name('payment-transactions.refund');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/confirm-payment', [AdminOrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/shipping/zones', [ShippingZoneController::class, 'index'])->name('shipping.zones.index');
        Route::post('/shipping/zones', [ShippingZoneController::class, 'store'])->name('shipping.zones.store');
        Route::put('/shipping/zones/{zone}', [ShippingZoneController::class, 'update'])->name('shipping.zones.update');

        Route::get('/shipping/methods', [ShippingMethodController::class, 'index'])->name('shipping.methods.index');
        Route::post('/shipping/methods', [ShippingMethodController::class, 'store'])->name('shipping.methods.store');
        Route::put('/shipping/methods/{method}', [ShippingMethodController::class, 'update'])->name('shipping.methods.update');
        Route::post('/shipping/methods/{method}/rate', [ShippingMethodController::class, 'setRate'])->name('shipping.methods.rate');

        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');

        Route::get('/restock-requests', [RestockRequestController::class, 'index'])->name('restock-requests.index');
        Route::post('/restock-requests', [RestockRequestController::class, 'store'])->name('restock-requests.store');
        Route::post('/restock-requests/{restockRequest}/approve', [RestockRequestController::class, 'approve'])->name('restock-requests.approve');
        Route::post('/restock-requests/{restockRequest}/reject', [RestockRequestController::class, 'reject'])->name('restock-requests.reject');
    });

});

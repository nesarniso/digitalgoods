# DigitalGoods

DigitalGoods is a Laravel 13 single-vendor ecommerce platform being developed
toward 6Valley-level functionality. This README intentionally replaces the
old phase notes and documents the current implementation and remaining work
only.

## Stack and local setup

- Laravel 13.30.1
- PHP 8.3.6
- SQLite for local development
- Blade + Tailwind CSS v4 + Vite
- Sanctum API authentication
- Spatie Laravel Permission
- BCMath for money calculations

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan db:seed --class=ShippingSeeder
npm install
npm run build
php artisan serve
```

Fresh database seeds load the bundled default catalog from
`database/seeders/data/default-catalog.json` and its referenced files from
`storage/app/public/media`. Keep both with the project distribution; the
snapshot contains catalog data only, not customer or order data.

To replace the current category tree with WorkLinc's public WooCommerce
categories, run `php artisan worklinc:import-categories`. This command
replaces old categories, moves products previously assigned to them into
`Digital Products`, and keeps category-scoped coupons attached to that
category.

After importing categories, run `php artisan worklinc:import-products` to
import all public WorkLinc product listings, INR prices, descriptions,
product images, and sample videos. The command can be re-run safely and
identifies products and media by their WorkLinc source IDs/URLs. The public
API does not include protected download files, so those must be added
separately before promising digital delivery.
Storefront and payment amounts use Indian Rupees (INR/₹).

## cPanel database package

This codebase does not currently provide a browser-based `/install` route.
The cPanel release package instead includes `cpanel-database-import.sql`, a
phpMyAdmin import for an empty MySQL database. It contains the current schema,
migration history, required role/permission and shipping setup, and catalog
records. It deliberately excludes user accounts, orders, settings, and
credentials. Product image files are included under
`storage/app/public/media/products`.

The release `.env` is a placeholder template, not a configured production
environment. Replace the database values and domain, generate a unique
`APP_KEY`, then create the first admin from the cPanel terminal with
`php artisan admin:create admin@example.com`. The command securely prompts
for a password.

## cPanel deployment checklist

1. Upload the project outside `public_html` when possible, and set the domain
   document root to the project's `public/` directory. If the host cannot use a
   custom document root, copy only the contents of `public/` into
   `public_html/` and update the paths in `public/index.php`.
2. Use PHP 8.3+ with `bcmath`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`,
   and `xml` enabled. Run `composer install --no-dev --optimize-autoloader`
   on the server, or upload the matching `vendor/` directory.
3. In cPanel, create the MySQL database and user, add the user to the database with **All Privileges**, and use the complete prefixed names cPanel shows (for example `accountname_ecommerce_core` and `accountname_ecommerce_user`). Then set production values in `.env`:
   `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, a new
   `APP_KEY`, MySQL credentials, and the mail/payment credentials you use.
4. If Redis is not available on the hosting plan, use
   `CACHE_STORE=file`, `SESSION_DRIVER=file`, and `QUEUE_CONNECTION=sync`.
   Otherwise configure Redis and run a queue worker for the `push` queue.
5. Import `cpanel-database-import.sql` into the empty database with phpMyAdmin.
   Then set the actual cPanel database credentials in `.env`, run
   `php artisan key:generate --force`, and create the first admin with
   `php artisan admin:create admin@example.com`. The provided archive already
   contains Composer dependencies and built frontend assets. Ensure
   `storage/` and `bootstrap/cache/` are writable by the PHP process, then run
   `php artisan optimize`.
6. Add a cPanel cron job that runs
   `php artisan schedule:run` every minute. Scheduled notifications and
   commerce automations depend on this.
7. Keep Firebase service-account JSON outside the public directory and set
   `FCM_ENABLED=true`, `FCM_CREDENTIALS_PATH`, and `FCM_PROJECT_ID` only when
   server-side push sending is configured.

### Hostinger hPanel quick path (eflix.store)

1. In **Websites → Manage → Databases → MySQL databases**, create a database
   and user, assign the user with all privileges, and copy the exact host,
   database name, username, and password shown by hPanel. Hostinger may prefix
   the database/user names; never reuse the local `.env` values.
2. Set the domain document root to the uploaded project's `public/` directory.
   If hPanel only exposes `public_html`, copy the contents of `public/` there
   and keep the rest of the Laravel project one directory above it.
3. Copy `.env.cpanel.example` to `.env`, replace the domain and every database
   value with the credentials shown by hPanel, and generate a new `APP_KEY`
   with `php artisan key:generate --force`.
4. For the packaged database, create an empty MySQL database and import
   `cpanel-database-import.sql` in phpMyAdmin. This import contains no user or
   order data.
5. From the project directory in hPanel Terminal, create the first admin with
   `php artisan admin:create admin@example.com`; enter a unique password when
   prompted. Then run `php artisan optimize` and verify `/` and `/admin/login`.

### Manual shared-hosting mode

For shared hosting, use the provided placeholder `.env`, import
`cpanel-database-import.sql` into an empty database, and fill in the exact
hPanel database credentials. Generate `APP_KEY` and create the first admin
from hPanel Terminal before opening the site. After changing `.env`, run
`php artisan optimize:clear`.

To import the public eflix.store WooCommerce catalog (including categories,
prices, descriptions, and local product/category images), run:

```bash
php artisan catalog:import-eflix --images
```

The importer is repeatable and updates existing eflix products by their
`EFLIX-{source id}` SKU.

To reduce oversized product image storage, first preview the conversion and
estimated savings, then run it with a WebP quality between 0 and 100 (90 is
the default). The command updates product image records and removes the source
file only after the optimized file is stored, so back up the database and
`storage/app/public/media` before running it on production:

```bash
php artisan media:optimize-products --dry-run
php artisan media:optimize-products --quality=90
```

If Redis is unavailable locally, use the file/sync drivers in `.env`:

```env
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

The local admin account is created with:

```bash
php artisan admin:create
```

Do not commit real passwords, API keys, courier credentials, or payment
secrets. Keep them in `.env`.

## Completed functionality

### Storefront and checkout

- Homepage, shop listing, product details, search, sorting, and filtering
- Responsive storefront layout
- Guest cart and authenticated cart
- Cart quantity update, item removal, coupon apply/remove
- Server-side pricing, tax, discount, and grand-total calculation
- Shipping method selection and checkout summary
- Guest and authenticated order placement
- Order success page
- Checkout payment method selection from `config/payments.php`

### Customer account

- Customer registration
- Email/phone password login through the existing auth service
- Session logout and remember-me login
- Account dashboard
- Order history
- Order detail page
- Order status timeline/tracking
- Customer order cancellation with ownership checks

### Product reviews

- Only authenticated customers can review a product included in their own
  delivered order; one review is allowed per product and order
- The server sets the verified-purchase flag after checking ownership,
  delivery status, and the purchased product; customers cannot self-assert it
- Rating is required; headline and written feedback are optional
- Reviews are moderated before display. Approved reviews show rating,
  verification status, date, and a privacy-conscious abbreviated author name
- Product pages show average rating, star distribution, rating filters, and
  newest/highest/lowest sorting. Moderation should remove policy-violating
  content, not legitimate reviews solely because they are negative

### Admin panel

- Admin session login/logout and role protection
- Dashboard and responsive sidebar
- Product create/edit, image upload, and variant creation
- Category create/edit/delete
- Brand create/edit/delete
- Attribute create/edit/delete
- Attribute value add/delete
- Coupon create/edit/delete
- Order list, order detail, and status update
- Inventory, warehouse, restock, customer, employee, settings, audit, and
  report screens
- Payment transaction list and detail
- Manual COD/bank-transfer confirmation
- Full and partial refunds
- Shipping method, zone, and rate configuration

### Shipping configuration

- Flat, free, weight-based, and price-based shipping methods
- Catch-all/default rates
- Country, division, district, and area zones
- Zone-specific rates
- Per-kg rate and free-above threshold
- `ShippingSeeder` creates:
  - `Standard delivery` at INR 50–100 depending on zone
  - `Free delivery` at INR 0

### Payments currently enabled

Enabled in `config/payments.php`:

- `cash_on_delivery`
- `bank_transfer`

The checkout key is `cash_on_delivery`; `cod` is invalid and must not be
reintroduced. Stripe, SSLCommerz, bKash, and Nagad are present as disabled
configuration entries only. They do not have production gateway adapters yet.

## Important routes

```text
/                         Storefront home
/shop                     Product listing
/cart                     Cart
/checkout                 Checkout
/account                  Customer account
/account/orders/{id}      Customer order tracking
/admin                    Admin dashboard
/admin/products/create    Product form
/admin/categories         Category management
/admin/attributes         Attribute management
/admin/shipping           Shipping configuration
/admin/payments           Payment management
```

API routes are versioned under `/api/v1`. Existing API areas include auth,
catalog, cart, checkout, orders, shipping, wishlist, compare, addresses,
admin catalog, inventory, payments, settings, and audit logs.

## Important implementation rules

1. Put business logic in domain services, not controllers.
2. Reuse `ProductService`, `OrderService`, `PaymentService`,
   `ShippingConfigService`, `AttributeService`, `CategoryService`,
   `BrandService`, and `CouponService`.
3. Never trust client-submitted prices; use `PricingService` and
   `CheckoutService`.
4. Use BCMath/decimal values for currency.
5. Payment mutations must go through `PaymentService`.
6. Order status changes must go through `OrderService`.
7. Product media uses the `media` disk and `/storage/media`.
8. Category routes use `categories`, not the invalid `categorys`.
9. Shipping checkout options require an active method with a matching rate.
10. Put external credentials in `.env` and gate integrations with config flags.

## Known fixes already applied

- Repaired Composer dependency/lock issues and installed Laravel dependencies.
- Fixed missing `vendor/autoload.php` setup.
- Added SQLite database creation and migration support.
- Added global login compatibility route for Laravel auth middleware.
- Fixed storefront admin login menu visibility.
- Fixed checkout `cod` versus `cash_on_delivery` payment-key mismatch.
- Fixed category CRUD form route generation from `categorys` to `categories`.
- Rebuilt frontend assets with Vite after storefront/checkout changes.

## Next work: courier and fulfilment

This is the next recommended phase.

1. Create shipment/fulfilment tables linked to orders.
2. Add shipment number, courier, delivery fee, pickup, dispatch, delivered,
   failed, returned, and cancelled statuses.
3. Add a `CourierGatewayInterface`.
4. Implement a manual/mock courier adapter first.
5. Add Pathao, Steadfast, and RedX adapters behind configuration flags.
6. Add admin shipment creation, pickup request, dispatch, and tracking UI.
7. Show courier and tracking information in customer order details.
8. Add webhook signature verification, retries, idempotency, and audit logs.
9. Add return-to-sender and failed-delivery handling.

Do not add real courier API calls until the interface, persistence, retries,
and webhook security are covered by tests.

## Later roadmap

- bKash, Nagad, SSLCommerz, and Stripe gateway adapters
- Customer payment receipts and payment failure recovery
- Returns/RMA and customer refund requests
- Reviews, ratings, notifications, email, and SMS
- Saved customer address UI
- Wishlist and compare storefront UI
- Vendor registration, approval, commission, vendor panel, and withdrawals
- Deliveryman panel and proof of delivery
- Wallet, loyalty, referral, and POS
- Multi-vendor reports
- Mobile apps
- Digital products and auction
- SEO, multi-language, and multi-currency

## Validation commands

Run targeted tests after each change:

```bash
php artisan view:cache
php artisan route:list
php artisan test tests/Feature/Shipping
php artisan test tests/Feature/Payments
php artisan test tests/Feature/Orders
php artisan test
npm run build
```

Before production, run the full test suite, replace local admin credentials,
configure real database/storage/mail/queue services, and verify all payment
and courier webhook signatures.

### cPanel deployment notes

Upload the project outside the public document root and point the domain
document root to its `public` directory. Do not upload generated files from
`bootstrap/cache` or `storage/framework/views`; they contain paths from the
build machine. After upload, run these commands from the Laravel project root:

```bash
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
php artisan optimize:clear
php artisan storage:link
php artisan view:cache
```

If SSH is unavailable, create the same directories in cPanel File Manager and
ensure the account user can write to `storage` and `bootstrap/cache`.

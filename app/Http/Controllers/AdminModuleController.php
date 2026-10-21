<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Content\Models\Page;
use App\Domain\Digital\Models\DigitalLicenseKey;
use App\Domain\Digital\Services\DigitalLicenseService;
use App\Domain\Commerce\Models\Coupon;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\RestockRequest;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Inventory\Services\InventoryService;
use App\Domain\Inventory\Services\WarehouseService;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Settings\Models\Setting;
use App\Domain\Settings\Services\SettingService;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Models\ShippingZone;
use App\Domain\Users\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Domain\Audit\Services\AuditLogger;

class AdminModuleController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly InventoryService $inventoryService,
        private readonly WarehouseService $warehouseService,
        private readonly DigitalLicenseService $digitalLicenses,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function categories()
    {
        return $this->categoryList('category');
    }

    public function subcategories()
    {
        return $this->categoryList('subcategory');
    }

    public function subSubcategories()
    {
        return $this->categoryList('sub-subcategory');
    }

    private function categoryList(string $level)
    {
        $categories = Category::query()
            ->withCount('products')
            ->with('parent.parent')
            ->when($level === 'category', fn ($query) => $query->whereNull('parent_id'))
            ->when($level === 'subcategory', fn ($query) => $query->whereHas('parent', fn ($parent) => $parent->whereNull('parent_id')))
            ->when($level === 'sub-subcategory', fn ($query) => $query->whereHas('parent.parent', fn ($parent) => $parent->whereNull('parent_id')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.modules.categories', compact('categories', 'level'));
    }

    public function inventory()
    {
        return view('admin.modules.inventory', [
            'inventory' => Inventory::query()->with(['product', 'warehouse'])->latest('id')->paginate(20),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function digitalProducts()
    {
        return view('admin.modules.digital-products', [
            'products' => Product::query()->where('product_type', 'digital')->with('variants.attributeValues.attribute')->orderBy('name')->get(['id', 'name', 'sku']),
            'licenses' => DigitalLicenseKey::query()->with(['product', 'variant.attributeValues.attribute', 'order'])->latest('id')->paginate(30),
        ]);
    }

    public function digitalLicenseStore(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'secret' => ['required', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ]);
        $product = Product::findOrFail($data['product_id']);
        $variant = ! empty($data['product_variant_id'])
            ? ProductVariant::query()->where('product_id', $product->id)->findOrFail($data['product_variant_id'])
            : null;
        $this->digitalLicenses->add($product, $data['secret'], $data['instructions'] ?? null, $request->user(), $variant);

        return back()->with('success', 'Digital license added to stock.');
    }

    public function digitalLicenseReveal(Request $request, DigitalLicenseKey $license)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin']) || $request->user()->can('digital_delivery.reveal'), 403);
        $this->auditLogger->log(
            actor: $request->user(),
            action: 'credential_revealed',
            module: 'DigitalDelivery',
            entityType: 'DigitalLicenseKey',
            entityId: (string) $license->id,
            newValue: ['reference' => $license->reference, 'order_id' => $license->order_id],
            request: $request,
        );

        return response()->json([
            'reference' => $license->reference,
            'secret' => $license->plain_secret,
            'status' => $license->status,
            'order_number' => $license->order?->order_number,
            'customer_email' => $license->order?->customer_email,
        ]);
    }

    public function digitalLicenseRevoke(Request $request, DigitalLicenseKey $license)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin']) || $request->user()->can('digital_delivery.revoke'), 403);
        $this->digitalLicenses->revoke($license, $request->user());

        return back()->with('success', 'Digital license revoked.');
    }

    public function digitalLicenseReissue(Request $request, DigitalLicenseKey $license)
    {
        abort_unless($request->user()->hasAnyRole(['super_admin', 'admin']) || $request->user()->can('digital_delivery.reissue'), 403);
        $data = $request->validate([
            'secret' => ['required', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
        ]);
        $this->digitalLicenses->reissue($license, $data['secret'], $data['instructions'] ?? null, $request->user());

        return back()->with('success', 'Digital license reissued.');
    }

    public function adjustInventory(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $inventory = $this->inventoryService->firstOrCreateInventory($product, null, $warehouse);
        $this->inventoryService->adjust($inventory, (int) $data['delta'], $data['reason'], $request->user());

        return back()->with('success', 'Inventory updated successfully.');
    }

    public function warehouseStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $this->warehouseService->create($data, $request->user());

        return back()->with('success', 'Warehouse created successfully.');
    }

    public function warehouses()
    {
        return view('admin.modules.warehouses', [
            'warehouses' => Warehouse::query()
                ->withCount('inventories')
                ->withSum('inventories', 'physical_stock')
                ->withSum('inventories', 'reserved_stock')
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function shipping()
    {
        return view('admin.modules.shipping', [
            'methods' => ShippingMethod::query()->withCount('rates')->orderBy('sort_order')->get(),
            'zones' => ShippingZone::query()->withCount('rates')->latest('id')->get(),
        ]);
    }

    public function couriers()
    {
        $drivers = config('couriers.drivers', []);
        $shipments = Schema::hasTable('shipments')
            ? Shipment::query()->with('order')->latest('id')->paginate(15)
            : collect();

        return view('admin.modules.couriers', [
            'drivers' => $drivers,
            'shipments' => $shipments,
        ]);
    }

    public function customers(Request $request)
    {
        return view('admin.modules.customers', [
            'customers' => User::query()->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'super_admin']))->withCount('orders')->latest('id')->paginate(20),
        ]);
    }

    public function employees()
    {
        return view('admin.modules.employees', [
            'employees' => EmployeeProfile::query()->with('user', 'reportsTo')->latest('id')->paginate(20),
        ]);
    }

    public function settings(Request $request)
    {
        return view('admin.modules.settings', [
            'settings' => Setting::query()->orderBy('group')->orderBy('key')->get()->groupBy('group'),
            'homepageSettings' => $this->homepageSettings(),
            'systemSettings' => $this->systemSettings(),
            'entrepreneurProfitPercent' => (float) $this->settings->get('entrepreneur.profit_percent', 20),
            'entrepreneurReturnCost' => (float) $this->settings->get('entrepreneur.return_cost', 0),
            'isHeroSection' => $request->query('section') === 'hero',
            'isHomePageSection' => $request->query('section') === 'home',
        ]);
    }

    public function paymentSettings()
    {
        $methods = ['cash_on_delivery', 'bkash', 'nagad', 'rocket', 'bank_transfer'];
        return view('admin.modules.payment-settings', [
            'accountTypes' => config('payments.manual_account_types', []),
            'paymentSettings' => collect($methods)->mapWithKeys(fn ($key) => [$key => [
                'enabled' => $this->settings->boolean("payments.{$key}.enabled", config("payments.methods.{$key}.enabled", false)),
                'merchant_number' => $this->settings->get("payments.{$key}.merchant_number", ''),
                'account_type' => $this->settings->get("payments.{$key}.account_type", 'merchant'),
                'bank_name' => $this->settings->get("payments.{$key}.bank_name", ''),
                'account_name' => $this->settings->get("payments.{$key}.account_name", ''),
                'account_number' => $this->settings->get("payments.{$key}.account_number", ''),
                'branch' => $this->settings->get("payments.{$key}.branch", ''),
                'routing_number' => $this->settings->get("payments.{$key}.routing_number", ''),
                'instructions' => $this->settings->get("payments.{$key}.instructions", $this->defaultPaymentInstruction('merchant')),
                'qr_code' => $this->settings->get("payments.{$key}.qr_code", ''),
            ]]),
        ]);
    }

    public function contactSettings()
    {
        return view('admin.modules.contact-settings', [
            'contactSettings' => $this->getContactSettings(),
        ]);
    }

    public function saveContactSettings(Request $request)
    {
        $data = $request->validate([
            'badge' => ['nullable', 'string', 'max:80'],
            'hero_title' => ['required', 'string', 'max:160'],
            'hero_text' => ['required', 'string', 'max:500'],
            'form_eyebrow' => ['nullable', 'string', 'max:80'],
            'form_title' => ['required', 'string', 'max:160'],
            'form_text' => ['required', 'string', 'max:500'],
            'support_hours' => ['required', 'string', 'max:255'],
            'response_time' => ['required', 'string', 'max:255'],
            'faq_eyebrow' => ['nullable', 'string', 'max:80'],
            'faq_title' => ['required', 'string', 'max:160'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['nullable', 'string', 'max:180'],
            'faq_items.*.answer' => ['nullable', 'string', 'max:500'],
        ]);

        foreach (['badge', 'hero_title', 'hero_text', 'form_eyebrow', 'form_title', 'form_text', 'support_hours', 'response_time', 'faq_eyebrow', 'faq_title'] as $field) {
            $this->settings->set("contact.{$field}", $data[$field] ?? '', 'contact', 'string', $request->user());
        }
        $faqItems = collect($data['faq_items'] ?? [])
            ->map(fn (array $item) => ['question' => trim($item['question'] ?? ''), 'answer' => trim($item['answer'] ?? '')])
            ->filter(fn (array $item) => $item['question'] !== '' && $item['answer'] !== '')
            ->values()
            ->all();
        $this->settings->set('contact.faq_items', json_encode($faqItems, JSON_UNESCAPED_UNICODE), 'contact', 'json', $request->user());

        return back()->with('success', 'Contact page settings updated.');
    }

    public function savePaymentSettings(Request $request)
    {
        $data = $request->validate([
            'bkash_merchant_number' => ['nullable', 'string', 'max:50'],
            'nagad_merchant_number' => ['nullable', 'string', 'max:50'],
            'rocket_merchant_number' => ['nullable', 'string', 'max:50'],
            'bank_transfer_account_number' => ['nullable', 'string', 'max:100'],
            'bank_transfer_bank_name' => ['nullable', 'string', 'max:255'],
            'bank_transfer_account_name' => ['nullable', 'string', 'max:255'],
            'bank_transfer_branch' => ['nullable', 'string', 'max:255'],
            'bank_transfer_routing_number' => ['nullable', 'string', 'max:100'],
            'bkash_account_type' => ['required', 'in:personal,merchant,agent'],
            'nagad_account_type' => ['required', 'in:personal,merchant,agent'],
            'rocket_account_type' => ['required', 'in:personal,merchant,agent'],
            'bkash_instructions' => ['nullable', 'string', 'max:1000'],
            'nagad_instructions' => ['nullable', 'string', 'max:1000'],
            'rocket_instructions' => ['nullable', 'string', 'max:1000'],
            'bank_transfer_instructions' => ['nullable', 'string', 'max:1000'],
            'bkash_qr_code' => ['nullable', 'image', 'max:4096'],
            'nagad_qr_code' => ['nullable', 'image', 'max:4096'],
            'rocket_qr_code' => ['nullable', 'image', 'max:4096'],
            'bank_transfer_qr_code' => ['nullable', 'image', 'max:4096'],
            'cash_on_delivery_enabled' => ['nullable', 'boolean'],
            'bkash_enabled' => ['nullable', 'boolean'],
            'nagad_enabled' => ['nullable', 'boolean'],
            'rocket_enabled' => ['nullable', 'boolean'],
            'bank_transfer_enabled' => ['nullable', 'boolean'],
        ]);
        foreach (['bkash', 'nagad', 'rocket', 'bank_transfer'] as $method) {
            $this->settings->set("payments.{$method}.enabled", $request->boolean("{$method}_enabled"), 'payments', 'boolean', $request->user());
            $this->settings->set("payments.{$method}.merchant_number", $data["{$method}_merchant_number"] ?? '', 'payments', 'string', $request->user());
            if ($method !== 'bank_transfer') {
                $accountType = $data["{$method}_account_type"];
                $this->settings->set("payments.{$method}.account_type", $accountType, 'payments', 'string', $request->user());
            }
            $this->settings->set('payments.cash_on_delivery.enabled', $request->boolean('cash_on_delivery_enabled'), 'payments', 'boolean', $request->user());
            $instructions = trim($data["{$method}_instructions"] ?? '')
                ?: ($method === 'bank_transfer'
                    ? 'Transfer the order total to the bank account below, then enter the payer mobile number and transaction ID.'
                    : $this->defaultPaymentInstruction($accountType));
            $this->settings->set("payments.{$method}.instructions", $instructions, 'payments', 'string', $request->user());
            foreach (['bank_name', 'account_name', 'account_number', 'branch', 'routing_number'] as $field) {
                if ($method === 'bank_transfer') {
                    $this->settings->set("payments.{$method}.{$field}", $data["{$method}_{$field}"] ?? '', 'payments', 'string', $request->user());
                }
            }
            if ($request->hasFile("{$method}_qr_code")) {
                $path = $request->file("{$method}_qr_code")->store('payments/qr', 'media');
                if ($path === false) {
                    throw new \RuntimeException("The {$method} QR code could not be stored.");
                }
                $this->settings->set("payments.{$method}.qr_code", $path, 'payments', 'string', $request->user());
            }
        }
        return back()->with('success', 'Payment settings saved.');
    }

    private function defaultPaymentInstruction(string $accountType): string
    {
        return str_replace(':account_type', config("payments.manual_account_types.{$accountType}", 'Merchant'), config('payments.manual_default_instruction'));
    }

    public function saveEntrepreneurProfitSettings(Request $request)
    {
        $data = $request->validate([
            'profit_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'return_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $this->settings->set('entrepreneur.profit_percent', $data['profit_percent'], 'entrepreneur', 'decimal', $request->user());
        $this->settings->set('entrepreneur.return_cost', $data['return_cost'], 'entrepreneur', 'decimal', $request->user());

        return back()->with('success', 'Entrepreneur profit settings updated.');
    }

    public function mostSellingSettings()
    {
        return view('admin.modules.most-selling-settings', [
            'mostSellingEnabled' => (bool) filter_var($this->settings->get('homepage.most_selling_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'mostSellingProductIds' => json_decode((string) $this->settings->get('homepage.most_selling_product_ids', '[]'), true) ?: [],
            'products' => Product::query()->where('status', 'published')->with(['category', 'images'])->orderBy('name')->get(),
        ]);
    }

    public function saveMostSellingSettings(Request $request)
    {
        $data = $request->validate([
            'most_selling_enabled' => ['nullable', 'boolean'],
            'most_selling_product_ids' => ['nullable', 'array', 'max:5'],
            'most_selling_product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        $ids = array_values(array_map('intval', $data['most_selling_product_ids'] ?? []));
        $this->settings->set('homepage.most_selling_enabled', (bool) ($data['most_selling_enabled'] ?? false), 'homepage', 'boolean', $request->user());
        $this->settings->set('homepage.most_selling_product_ids', json_encode($ids), 'homepage', 'json', $request->user());

        return back()->with('success', 'Most Selling products updated.');
    }

    public function recommendedProductSettings()
    {
        return view('admin.modules.recommended-product-settings', [
            'recommendedProductId' => (int) $this->settings->get('homepage.recommended_product_id', 0),
            'latestProductIds' => json_decode((string) $this->settings->get('homepage.latest_product_ids', '[]'), true) ?: [],
            'products' => Product::query()
                ->where('status', 'published')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function saveRecommendedProductSettings(Request $request)
    {
        $data = $request->validate([
            'recommended_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'latest_product_ids' => ['nullable', 'array', 'max:12'],
            'latest_product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        $this->settings->set(
            'homepage.recommended_product_id',
            (int) ($data['recommended_product_id'] ?? 0),
            'homepage',
            'integer',
            $request->user()
        );
        $latestProductIds = array_values(array_map('intval', $data['latest_product_ids'] ?? []));
        $this->settings->set('homepage.latest_product_ids', json_encode($latestProductIds), 'homepage', 'json', $request->user());

        return back()->with('success', 'Recommended product updated.');
    }

    public function exportDatabase(Request $request)
    {
        abort_unless(config('database.default') === 'mysql', 422, 'Database export currently supports MySQL databases.');

        $backupPath = tempnam(storage_path('app/private'), 'database-export-');
        abort_unless(is_string($backupPath), 500, 'Unable to create database backup.');
        $connection = config('database.connections.mysql');
        $result = $this->runDatabaseCommand([
            'mysqldump',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--no-tablespaces',
            '--routines',
            '--triggers',
            $connection['database'],
        ], $connection, null, $backupPath);
        abort_if($result['exit_code'] !== 0, 500, 'Unable to export the MySQL database: '.$result['error']);

        return response()->download(
            $backupPath,
            'database-backup-'.now()->format('Y-m-d-His').'.sql',
            ['Content-Type' => 'application/sql'],
        )->deleteFileAfterSend(true);
    }

    public function importDatabase(Request $request)
    {
        abort_unless(config('database.default') === 'mysql', 422, 'Database import currently supports MySQL databases.');

        $data = $request->validate([
            'database' => ['required', 'file', 'mimes:sql,txt', 'max:1048576'],
        ]);

        $connection = config('database.connections.mysql');
        $backupDirectory = storage_path('app/private/database-backups');
        File::ensureDirectoryExists($backupDirectory);
        $backupPath = $backupDirectory.'/pre-import-'.now()->format('Y-m-d-His').'-'.bin2hex(random_bytes(4)).'.sql';

        $backupResult = $this->runDatabaseCommand([
            'mysqldump',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--no-tablespaces',
            '--routines',
            '--triggers',
            $connection['database'],
        ], $connection, null, $backupPath);
        abort_if(
            $backupResult['exit_code'] !== 0,
            500,
            'Database import was not started because the automatic pre-import backup failed: '.$backupResult['error']
        );

        $result = $this->runDatabaseCommand([
            'mysql',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            $connection['database'],
        ], $connection, $data['database']->getRealPath());
        abort_if(
            $result['exit_code'] !== 0,
            422,
            'Unable to import the MySQL backup: '.$result['error'].'. A pre-import backup was saved as '.basename($backupPath).'.'
        );

        return redirect()->route('admin.settings')->with(
            'success',
            'Database imported successfully. Pre-import backup retained as '.basename($backupPath).'. Please sign in again if your session expired.'
        );
    }

    private function runDatabaseCommand(array $command, array $connection, ?string $inputPath = null, ?string $outputPath = null): array
    {
        $input = $inputPath ? fopen($inputPath, 'rb') : null;
        $output = $outputPath ? fopen($outputPath, 'wb') : null;
        $error = tmpfile();
        abort_unless($inputPath === null || is_resource($input), 500, 'Unable to read the database backup.');
        abort_unless($outputPath === null || is_resource($output), 500, 'Unable to write the database backup.');
        abort_unless(is_resource($error), 500, 'Unable to prepare database command.');

        $process = proc_open($command, [
            0 => $input ?: ['pipe', 'r'],
            1 => $output ?: ['pipe', 'w'],
            2 => $error,
        ], $pipes, base_path(), [
            'MYSQL_PWD' => (string) $connection['password'],
        ]);
        abort_unless(is_resource($process), 500, 'Unable to start the database command.');

        if (!$input) {
            fclose($pipes[0]);
        }
        if (!$output) {
            stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        }
        $exitCode = proc_close($process);
        $errorMessage = stream_get_contents($error) ?: '';
        fclose($error);
        if ($input) {
            fclose($input);
        }
        if ($output) {
            fclose($output);
        }

        return ['exit_code' => $exitCode, 'error' => trim($errorMessage)];
    }

    public function saveSystemSettings(Request $request)
    {
        $data = $request->validate([
            'brand_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_name' => ['required', 'string', 'max:100'],
            'slogan' => ['nullable', 'string', 'max:160'],
            'site_url' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'website_icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico', 'max:1024'],
            'delete_logo' => ['nullable', 'boolean'],
            'delete_website_icon' => ['nullable', 'boolean'],
        ]);

        $this->settings->set('system.brand_color', strtolower($data['brand_color']), 'system', 'string', $request->user());
        $this->settings->set('system.site_name', $data['site_name'], 'system', 'string', $request->user());
        $this->settings->set('system.slogan', $data['slogan'] ?? '', 'system', 'string', $request->user());
        $this->settings->set('system.site_url', $data['site_url'] ?? '', 'system', 'string', $request->user());
        $existingSystemSettings = $this->systemSettings();
        $this->settings->set('system.phone', $request->exists('phone') ? ($data['phone'] ?? '') : $existingSystemSettings['phone'], 'system', 'string', $request->user());
        $this->settings->set('system.email', $request->exists('email') ? ($data['email'] ?? '') : $existingSystemSettings['email'], 'system', 'string', $request->user());
        $this->settings->set('system.whatsapp', $request->exists('whatsapp') ? ($data['whatsapp'] ?? '') : $existingSystemSettings['whatsapp'], 'system', 'string', $request->user());
        $this->settings->set('system.address', $request->exists('address') ? ($data['address'] ?? '') : $existingSystemSettings['address'], 'system', 'string', $request->user());

        $media = \Illuminate\Support\Facades\Storage::disk('media');
        if ($request->boolean('delete_logo') && $existingSystemSettings['logo']) {
            $media->delete($existingSystemSettings['logo']);
            $this->settings->set('system.logo', '', 'system', 'string', $request->user());
        }

        if ($request->boolean('delete_website_icon') && $existingSystemSettings['website_icon']) {
            $media->delete($existingSystemSettings['website_icon']);
            $this->settings->set('system.website_icon', '', 'system', 'string', $request->user());
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding', 'media');
            if ($logoPath === false) {
                return back()->withErrors(['logo' => 'The logo could not be stored.']);
            }
            $this->settings->set('system.logo', $logoPath, 'system', 'string', $request->user());
        }
        if ($request->hasFile('website_icon')) {
            $iconPath = $request->file('website_icon')->store('branding', 'media');
            if ($iconPath === false) {
                return back()->withErrors(['website_icon' => 'The website icon could not be stored.']);
            }
            $this->settings->set('system.website_icon', $iconPath, 'system', 'string', $request->user());
        }

        return back()->with('success', 'System settings updated.');
    }

    public function headerMenuSettings()
    {
        return view('admin.modules.header-menu-settings', [
            'headerMenus' => collect(['home', 'shop', 'entrepreneur', 'contact', 'categories'])->map(fn (string $key) => [
                'key' => $key,
                'label' => $this->settings->get("header.{$key}_label", ucfirst($key)),
                'url' => $this->settings->get("header.{$key}_url", $key === 'home' ? '/' : ($key === 'contact' ? '/contact' : ($key === 'entrepreneur' ? '/entrepreneur/register' : '/shop'))),
                'enabled' => $this->settings->boolean("header.{$key}_enabled", true),
            ])->all(),
        ]);
    }

    public function saveHeaderMenuSettings(Request $request)
    {
        $data = $request->validate([
            'menus' => ['required', 'array', 'size:5'],
            'menus.*.key' => ['required', 'in:home,shop,entrepreneur,contact,categories'],
            'menus.*.label' => ['required', 'string', 'max:50'],
            'menus.*.url' => ['required', 'string', 'max:255'],
            'menus.*.enabled' => ['nullable', 'boolean'],
        ]);

        foreach ($data['menus'] as $menu) {
            $key = $menu['key'];
            $this->settings->set("header.{$key}_label", $menu['label'], 'header', 'string', $request->user());
            $this->settings->set("header.{$key}_url", $menu['url'], 'header', 'string', $request->user());
            $this->settings->set("header.{$key}_enabled", (bool) ($menu['enabled'] ?? false), 'header', 'boolean', $request->user());
        }

        return back()->with('success', 'Header menu settings updated.');
    }

    public function saveHomepageSettings(Request $request)
    {
        $data = $request->validate([
            'settings_scope' => ['nullable', 'in:hero,home'],
            'hero_enabled' => ['nullable', 'boolean'],
            'hero_title' => ['nullable', 'string', 'max:120'],
            'hero_subtitle' => ['nullable', 'string', 'max:220'],
            'hero_button_label' => ['nullable', 'string', 'max:50'],
            'hero_badge' => ['nullable', 'string', 'max:80'],
            'hero_background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'hero_overlay_enabled' => ['nullable', 'boolean'],
            'hero_primary_button_enabled' => ['nullable', 'boolean'],
            'hero_primary_button_label' => ['nullable', 'string', 'max:50'],
            'hero_primary_button_url' => ['nullable', 'string', 'max:255'],
            'hero_secondary_button_enabled' => ['nullable', 'boolean'],
            'hero_secondary_button_label' => ['nullable', 'string', 'max:50'],
            'hero_secondary_button_url' => ['nullable', 'string', 'max:255'],
            'slider_image_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'slider_image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'slider_image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'stats_section_enabled' => ['nullable', 'boolean'],
            'entrepreneur_section_enabled' => ['nullable', 'boolean'],
            'category_section_enabled' => ['nullable', 'boolean'],
            'most_selling_section_enabled' => ['nullable', 'boolean'],
            'products_section_enabled' => ['nullable', 'boolean'],
            'benefits_section_enabled' => ['nullable', 'boolean'],
            'why_section_enabled' => ['nullable', 'boolean'],
            'sell_section_enabled' => ['nullable', 'boolean'],
            'how_section_enabled' => ['nullable', 'boolean'],
            'cta_section_enabled' => ['nullable', 'boolean'],
            'stats_labels' => ['nullable', 'array'],
            'stats_labels.*' => ['nullable', 'string', 'max:60'],
            'entrepreneur_eyebrow' => ['nullable', 'string', 'max:80'],
            'entrepreneur_title' => ['nullable', 'string', 'max:160'],
            'entrepreneur_text' => ['nullable', 'string', 'max:300'],
            'category_eyebrow' => ['nullable', 'string', 'max:80'],
            'category_title' => ['nullable', 'string', 'max:100'],
            'most_selling_eyebrow' => ['nullable', 'string', 'max:80'],
            'most_selling_title' => ['nullable', 'string', 'max:100'],
            'products_eyebrow' => ['nullable', 'string', 'max:80'],
            'products_title' => ['nullable', 'string', 'max:100'],
            'benefit_titles' => ['nullable', 'array'],
            'benefit_titles.*' => ['nullable', 'string', 'max:80'],
            'benefit_texts' => ['nullable', 'array'],
            'benefit_texts.*' => ['nullable', 'string', 'max:160'],
            'entrepreneur_steps' => ['nullable', 'array'],
            'why_eyebrow' => ['nullable', 'string', 'max:80'],
            'why_title' => ['nullable', 'string', 'max:160'],
            'why_items' => ['nullable', 'array'],
            'faq_eyebrow' => ['nullable', 'string', 'max:80'],
            'faq_title' => ['nullable', 'string', 'max:100'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.title' => ['nullable', 'string', 'max:160'],
            'faq_items.*.text' => ['nullable', 'string', 'max:300'],
            'sell_eyebrow' => ['nullable', 'string', 'max:80'],
            'sell_title' => ['nullable', 'string', 'max:160'],
            'sell_text' => ['nullable', 'string', 'max:300'],
            'sell_items' => ['nullable', 'array'],
            'sell_drop_title' => ['nullable', 'string', 'max:100'],
            'sell_drop_text' => ['nullable', 'string', 'max:300'],
            'how_eyebrow' => ['nullable', 'string', 'max:80'],
            'how_title' => ['nullable', 'string', 'max:160'],
            'how_steps' => ['nullable', 'array'],
            'cta_eyebrow' => ['nullable', 'string', 'max:80'],
            'cta_title' => ['nullable', 'string', 'max:160'],
            'cta_text' => ['nullable', 'string', 'max:300'],
            'cta_button' => ['nullable', 'string', 'max:80'],
        ]);

        if ($data['settings_scope'] ?? null) {
            if ($data['settings_scope'] === 'hero') {
                $this->saveHeroSettingsScoped($request, $data);

                return back()->with('success', 'Hero settings updated.');
            }

            $this->saveHomeSettingsScoped($request, $data);

            return back()->with('success', 'Homepage settings updated.');
        }

        $this->settings->set('homepage.hero_enabled', (bool) ($data['hero_enabled'] ?? true), 'homepage', 'boolean', $request->user());
        $this->settings->set('homepage.hero_title', $data['hero_title'] ?? 'Everyday products, delivered simply.', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_subtitle', $data['hero_subtitle'] ?? 'Discover thoughtfully selected products, transparent pricing and dependable delivery from one trusted store.', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_button_label', $data['hero_button_label'] ?? 'Explore the shop', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_badge', $data['hero_badge'] ?? 'eflix Entrepreneur Platform', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_background_color', strtolower($data['hero_background_color'] ?? '#f8e4d0'), 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_overlay_enabled', (bool) ($data['hero_overlay_enabled'] ?? true), 'homepage', 'boolean', $request->user());
        $this->settings->set('homepage.hero_primary_button_enabled', (bool) ($data['hero_primary_button_enabled'] ?? true), 'homepage', 'boolean', $request->user());
        $this->settings->set('homepage.hero_primary_button_label', $data['hero_primary_button_label'] ?? ($data['hero_button_label'] ?? 'Explore the shop'), 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_primary_button_url', $data['hero_primary_button_url'] ?? '/shop', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_secondary_button_enabled', (bool) ($data['hero_secondary_button_enabled'] ?? true), 'homepage', 'boolean', $request->user());
        $this->settings->set('homepage.hero_secondary_button_label', $data['hero_secondary_button_label'] ?? 'Become an Entrepreneur', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.hero_secondary_button_url', $data['hero_secondary_button_url'] ?? '/entrepreneur/register', 'homepage', 'string', $request->user());
        $this->settings->set('homepage.slider_image_1', $this->storeHomepageImage($request->file('slider_image_1'), 'homepage.slider_image_1'), 'homepage', 'string', $request->user());
        $this->settings->set('homepage.slider_image_2', $this->storeHomepageImage($request->file('slider_image_2'), 'homepage.slider_image_2'), 'homepage', 'string', $request->user());
        $this->settings->set('homepage.slider_image_3', $this->storeHomepageImage($request->file('slider_image_3'), 'homepage.slider_image_3'), 'homepage', 'string', $request->user());
        foreach ([
            'stats_section_enabled', 'entrepreneur_section_enabled', 'category_section_enabled',
            'most_selling_section_enabled', 'products_section_enabled', 'benefits_section_enabled',
            'why_section_enabled', 'sell_section_enabled', 'how_section_enabled', 'cta_section_enabled',
        ] as $key) {
            $this->settings->set("homepage.{$key}", (bool) ($data[$key] ?? false), 'homepage', 'boolean', $request->user());
        }
        foreach (['entrepreneur_eyebrow', 'entrepreneur_title', 'entrepreneur_text', 'category_eyebrow', 'category_title', 'most_selling_eyebrow', 'most_selling_title', 'products_eyebrow', 'products_title'] as $key) {
            $this->settings->set("homepage.{$key}", $data[$key] ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['stats_labels'] ?? []) as $index => $label) {
            $this->settings->set("homepage.stats_label_{$index}", $label ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['benefit_titles'] ?? []) as $index => $label) {
            $this->settings->set("homepage.benefit_title_{$index}", $label ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['benefit_texts'] ?? []) as $index => $text) {
            $this->settings->set("homepage.benefit_text_{$index}", $text ?? '', 'homepage', 'string', $request->user());
        }
        foreach (['entrepreneur_steps' => 'entrepreneur_step', 'why_items' => 'why_item', 'faq_items' => 'faq_item', 'sell_items' => 'sell_item', 'how_steps' => 'how_step'] as $field => $prefix) {
            if ($field === 'faq_items') {
                $this->settings->set('homepage.faq_item_count', count($data[$field] ?? []), 'homepage', 'integer', $request->user());
            }
            foreach (($data[$field] ?? []) as $index => $item) {
                $this->settings->set("homepage.{$prefix}_title_{$index}", $item['title'] ?? '', 'homepage', 'string', $request->user());
                $this->settings->set("homepage.{$prefix}_text_{$index}", $item['text'] ?? '', 'homepage', 'string', $request->user());
            }
        }
        foreach (['why_eyebrow', 'why_title', 'faq_eyebrow', 'faq_title', 'sell_eyebrow', 'sell_title', 'sell_text', 'sell_drop_title', 'sell_drop_text', 'how_eyebrow', 'how_title', 'cta_eyebrow', 'cta_title', 'cta_text', 'cta_button'] as $key) {
            $this->settings->set("homepage.{$key}", $data[$key] ?? '', 'homepage', 'string', $request->user());
        }
        return back()->with('success', 'Homepage settings updated.');
    }

    private function saveHeroSettingsScoped(Request $request, array $data): void
    {
        foreach ([
            'hero_enabled', 'hero_overlay_enabled', 'hero_primary_button_enabled', 'hero_secondary_button_enabled',
        ] as $key) {
            $this->settings->set("homepage.{$key}", $request->boolean($key), 'homepage', 'boolean', $request->user());
        }
        foreach ([
            'hero_title', 'hero_subtitle', 'hero_button_label', 'hero_badge', 'hero_background_color',
            'hero_primary_button_label', 'hero_primary_button_url', 'hero_secondary_button_label', 'hero_secondary_button_url',
        ] as $key) {
            $this->settings->set("homepage.{$key}", $data[$key] ?? '', 'homepage', 'string', $request->user());
        }
        foreach (['slider_image_1', 'slider_image_2', 'slider_image_3'] as $key) {
            $path = $this->storeHomepageImage($request->file($key), "homepage.{$key}");
            if ($path !== null) {
                $this->settings->set("homepage.{$key}", $path, 'homepage', 'string', $request->user());
            }
        }
    }

    private function saveHomeSettingsScoped(Request $request, array $data): void
    {
        foreach ([
            'stats_section_enabled', 'entrepreneur_section_enabled', 'category_section_enabled',
            'most_selling_section_enabled', 'products_section_enabled', 'benefits_section_enabled',
            'why_section_enabled', 'sell_section_enabled', 'how_section_enabled', 'cta_section_enabled',
        ] as $key) {
            $this->settings->set("homepage.{$key}", $request->boolean($key), 'homepage', 'boolean', $request->user());
        }
        foreach ([
            'entrepreneur_eyebrow', 'entrepreneur_title', 'entrepreneur_text', 'category_eyebrow', 'category_title',
            'most_selling_eyebrow', 'most_selling_title', 'products_eyebrow', 'products_title', 'why_eyebrow', 'why_title',
            'faq_eyebrow', 'faq_title', 'sell_eyebrow', 'sell_title', 'sell_text', 'sell_drop_title', 'sell_drop_text',
            'how_eyebrow', 'how_title', 'cta_eyebrow', 'cta_title', 'cta_text', 'cta_button',
        ] as $key) {
            $this->settings->set("homepage.{$key}", $data[$key] ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['stats_labels'] ?? []) as $index => $label) {
            $this->settings->set("homepage.stats_label_{$index}", $label ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['benefit_titles'] ?? []) as $index => $label) {
            $this->settings->set("homepage.benefit_title_{$index}", $label ?? '', 'homepage', 'string', $request->user());
        }
        foreach (($data['benefit_texts'] ?? []) as $index => $text) {
            $this->settings->set("homepage.benefit_text_{$index}", $text ?? '', 'homepage', 'string', $request->user());
        }
        foreach (['entrepreneur_steps' => 'entrepreneur_step', 'why_items' => 'why_item', 'faq_items' => 'faq_item', 'sell_items' => 'sell_item', 'how_steps' => 'how_step'] as $field => $prefix) {
            if ($field === 'faq_items') {
                $this->settings->set('homepage.faq_item_count', count($data[$field] ?? []), 'homepage', 'integer', $request->user());
            }
            foreach (($data[$field] ?? []) as $index => $item) {
                $this->settings->set("homepage.{$prefix}_title_{$index}", $item['title'] ?? '', 'homepage', 'string', $request->user());
                $this->settings->set("homepage.{$prefix}_text_{$index}", $item['text'] ?? '', 'homepage', 'string', $request->user());
            }
        }
    }

    public function footerSettingsPage()
    {
        return view('admin.modules.footer-settings', [
            'footerSettings' => $this->footerSettings(),
            'footerPages' => Page::query()->orderBy('title')->get(),
        ]);
    }

    public function saveFooterSettings(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:300'],
            'copyright' => ['nullable', 'string', 'max:150'],
            'newsletter_title' => ['nullable', 'string', 'max:100'],
            'newsletter_text' => ['nullable', 'string', 'max:200'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'column_one_title' => ['nullable', 'string', 'max:60'],
            'column_two_title' => ['nullable', 'string', 'max:60'],
            'footer_page_ids' => ['nullable', 'array'],
            'footer_page_ids.*' => ['integer', 'exists:pages,id'],
        ]);

        $fields = [
            'enabled' => [(bool) ($data['enabled'] ?? false), 'boolean'],
            'description' => [$data['description'] ?? '', 'string'],
            'copyright' => [$data['copyright'] ?? '', 'string'],
            'newsletter_title' => [$data['newsletter_title'] ?? '', 'string'],
            'newsletter_text' => [$data['newsletter_text'] ?? '', 'string'],
            'facebook_url' => [$data['facebook_url'] ?? '', 'string'],
            'instagram_url' => [$data['instagram_url'] ?? '', 'string'],
            'youtube_url' => [$data['youtube_url'] ?? '', 'string'],
            'column_one_title' => [$data['column_one_title'] ?? '', 'string'],
            'column_two_title' => [$data['column_two_title'] ?? '', 'string'],
        ];

        foreach ($fields as $key => [$value, $type]) {
            $this->settings->set("footer.{$key}", $value, 'footer', $type, $request->user());
        }

        foreach (['one', 'two'] as $column) {
            foreach (range(1, 3) as $index) {
                $this->settings->set("footer.column_{$column}_link_{$index}_label", $request->input("column_{$column}_link_{$index}_label", ''), 'footer', 'string', $request->user());
                $this->settings->set("footer.column_{$column}_link_{$index}_url", $request->input("column_{$column}_link_{$index}_url", ''), 'footer', 'string', $request->user());
            }
        }

        Page::query()->update(['show_in_footer' => false]);
        Page::query()
            ->whereIn('id', $data['footer_page_ids'] ?? [])
            ->update(['show_in_footer' => true]);

        return back()->with('success', 'Footer settings updated.');
    }

    private function getContactSettings(): array
    {
        $faqItems = json_decode((string) $this->settings->get('contact.faq_items', ''), true);

        return [
            'badge' => $this->settings->get('contact.badge', 'Customer care'),
            'hero_title' => $this->settings->get('contact.hero_title', 'We are here to help.'),
            'hero_text' => $this->settings->get('contact.hero_text', 'Have a question about a product or order? Reach out and our team will get back to you as soon as possible.'),
            'form_eyebrow' => $this->settings->get('contact.form_eyebrow', 'Send a message'),
            'form_title' => $this->settings->get('contact.form_title', 'How can we help?'),
            'form_text' => $this->settings->get('contact.form_text', 'Share the details below. We normally respond within 24 hours.'),
            'support_hours' => $this->settings->get('contact.support_hours', 'Saturday–Thursday, 10:00 AM–8:00 PM (BST)'),
            'response_time' => $this->settings->get('contact.response_time', 'We usually reply within 24 hours.'),
            'faq_eyebrow' => $this->settings->get('contact.faq_eyebrow', 'Quick answers'),
            'faq_title' => $this->settings->get('contact.faq_title', 'Before you contact us'),
            'faq_items' => is_array($faqItems) ? $faqItems : [
                ['question' => 'How do I track my order?', 'answer' => 'Keep your order number ready and choose Order support in the form above.'],
                ['question' => 'Can I request a return or refund?', 'answer' => 'Yes. Select Returns and refunds and include your order number and the reason.'],
                ['question' => 'What payment methods do you accept?', 'answer' => 'Our available payment options are shown securely during checkout.'],
            ],
        ];
    }

    private function homepageSettings(): array
    {
        return [
            'hero_enabled' => (bool) filter_var($this->settings->get('homepage.hero_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'hero_title' => $this->settings->get('homepage.hero_title', 'Everyday products, delivered simply.'),
            'hero_subtitle' => $this->settings->get('homepage.hero_subtitle', 'Discover thoughtfully selected products, transparent pricing and dependable delivery from one trusted store.'),
            'hero_button_label' => $this->settings->get('homepage.hero_button_label', 'Explore the shop'),
            'hero_badge' => $this->settings->get('homepage.hero_badge', 'eflix Entrepreneur Platform'),
            'hero_background_color' => $this->settings->get('homepage.hero_background_color', '#f8e4d0'),
            'hero_overlay_enabled' => (bool) filter_var($this->settings->get('homepage.hero_overlay_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'hero_primary_button_enabled' => (bool) filter_var($this->settings->get('homepage.hero_primary_button_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'hero_primary_button_label' => $this->settings->get('homepage.hero_primary_button_label', 'Explore the shop'),
            'hero_primary_button_url' => $this->settings->get('homepage.hero_primary_button_url', '/shop'),
            'hero_secondary_button_enabled' => (bool) filter_var($this->settings->get('homepage.hero_secondary_button_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'hero_secondary_button_label' => $this->settings->get('homepage.hero_secondary_button_label', 'Become an Entrepreneur'),
            'hero_secondary_button_url' => $this->settings->get('homepage.hero_secondary_button_url', '/entrepreneur/register'),
            'slider_image_1' => $this->settings->get('homepage.slider_image_1'),
            'slider_image_2' => $this->settings->get('homepage.slider_image_2'),
            'slider_image_3' => $this->settings->get('homepage.slider_image_3'),
            'category_section_enabled' => (bool) filter_var($this->settings->get('homepage.category_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'category_limit' => max(4, (int) $this->settings->get('homepage.category_limit', 8)),
            'featured_section_enabled' => (bool) filter_var($this->settings->get('homepage.featured_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'featured_limit' => max(4, (int) $this->settings->get('homepage.featured_limit', 8)),
            'stats_section_enabled' => (bool) filter_var($this->settings->get('homepage.stats_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'entrepreneur_section_enabled' => (bool) filter_var($this->settings->get('homepage.entrepreneur_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'most_selling_section_enabled' => (bool) filter_var($this->settings->get('homepage.most_selling_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'products_section_enabled' => (bool) filter_var($this->settings->get('homepage.products_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'benefits_section_enabled' => (bool) filter_var($this->settings->get('homepage.benefits_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'why_section_enabled' => (bool) filter_var($this->settings->get('homepage.why_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'sell_section_enabled' => (bool) filter_var($this->settings->get('homepage.sell_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'how_section_enabled' => (bool) filter_var($this->settings->get('homepage.how_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'cta_section_enabled' => (bool) filter_var($this->settings->get('homepage.cta_section_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'stats_labels' => array_map(fn ($index, $default) => $this->settings->get("homepage.stats_label_{$index}", $default), array_keys(['Products available', 'Active entrepreneurs', 'Successful orders', 'Product categories']), ['Products available', 'Active entrepreneurs', 'Successful orders', 'Product categories']),
            'entrepreneur_eyebrow' => $this->settings->get('homepage.entrepreneur_eyebrow', 'Build your business with eflix'),
            'entrepreneur_title' => $this->settings->get('homepage.entrepreneur_title', 'Products আছে, market করুন, profit তৈরি করুন'),
            'entrepreneur_text' => $this->settings->get('homepage.entrepreneur_text', 'Marketplace থেকে product select করে নিজের selling link share করুন অথবা customer order manually submit করুন। Delivery ও operations eflix manage করবে।'),
            'category_eyebrow' => $this->settings->get('homepage.category_eyebrow', 'Start shopping'),
            'category_title' => $this->settings->get('homepage.category_title', 'Shop by category'),
            'most_selling_eyebrow' => $this->settings->get('homepage.most_selling_eyebrow', 'Customer favorites'),
            'most_selling_title' => $this->settings->get('homepage.most_selling_title', 'Most Selling'),
            'products_eyebrow' => $this->settings->get('homepage.products_eyebrow', 'Fresh arrivals'),
            'products_title' => $this->settings->get('homepage.products_title', 'Latest Products'),
            'benefit_titles' => array_map(fn ($index, $default) => $this->settings->get("homepage.benefit_title_{$index}", $default), array_keys(['Fast delivery', 'Easy returns', 'Secure payment', 'Helpful support']), ['Fast delivery', 'Easy returns', 'Secure payment', 'Helpful support']),
            'benefit_texts' => array_map(fn ($index, $default) => $this->settings->get("homepage.benefit_text_{$index}", $default), array_keys(['Reliable delivery to your door.', 'Simple support when you need it.', 'Your payment details stay protected.', 'We are here when you need help.']), ['Reliable delivery to your door.', 'Simple support when you need it.', 'Your payment details stay protected.', 'We are here when you need help.']),
            'entrepreneur_steps' => $this->homepageTextGroup(['Select products', 'Market anywhere', 'Earn from sales'], ['eflix catalog থেকে products বেছে নিন।', 'Online link বা offline order-এর মাধ্যমে sell করুন।', 'Successful sale-এর profit wallet-এ পান।'], 'entrepreneur_step'),
            'why_eyebrow' => $this->settings->get('homepage.why_eyebrow', 'Why eflix?'),
            'why_title' => $this->settings->get('homepage.why_title', 'আপনার business growth-এর জন্য সব support এক জায়গায়'),
            'why_items' => $this->homepageTextGroup(['500+ products', 'Marketing materials', 'Training support', 'Profit tracking'], ['বিভিন্ন category থেকে winning products বেছে নিন।', 'Captions, images এবং selling links দিয়ে দ্রুত শুরু করুন।', 'Dropshipping, marketing ও customer communication শিখুন।', 'প্রতিটি successful sale-এর profit wallet-এ track করুন।'], 'why_item'),
            'faq_eyebrow' => $this->settings->get('homepage.faq_eyebrow', 'FAQ'),
            'faq_title' => $this->settings->get('homepage.faq_title', 'সাধারণ প্রশ্ন'),
            'faq_items' => $this->homepageTextGroup(['কীভাবে sale track হবে?', 'Profit কখন পাওয়া যাবে?'], ['Order confirmation এবং account history থেকে আপনার order status ও delivery updates দেখতে পারবেন।', 'Successful order এবং return verification-এর পর eligible refund বা adjustment process করা হবে।'], 'faq_item'),
            'sell_eyebrow' => $this->settings->get('homepage.sell_eyebrow', 'Sell your way'),
            'sell_title' => $this->settings->get('homepage.sell_title', 'Online বা offline — business শুরু করুন নিজের মতো'),
            'sell_text' => $this->settings->get('homepage.sell_text', 'নিজের social network, local community বা personal contacts ব্যবহার করে eflix products-এর customer তৈরি করুন।'),
            'sell_items' => $this->homepageTextGroup(['Online marketing', 'Offline marketing'], ['Selling link copy করে Facebook, TikTok, WhatsApp বা অন্য platform-এ share করুন।', 'পরিচিত customer-এর order dashboard থেকে submit করে sale attribution নিশ্চিত করুন।'], 'sell_item'),
            'sell_drop_title' => $this->settings->get('homepage.sell_drop_title', 'Dropshipping কী?'),
            'sell_drop_text' => $this->settings->get('homepage.sell_drop_text', 'আপনাকে আগে থেকে product stock বা warehouse রাখতে হবে না। Customer order-এর পর eflix processing, delivery এবং support manage করবে; আপনি marketing ও sales-এ focus করবেন।'),
            'how_eyebrow' => $this->settings->get('homepage.how_eyebrow', 'How to start'),
            'how_title' => $this->settings->get('homepage.how_title', 'Registration থেকে first sale — eflix আপনার পাশে'),
            'how_steps' => $this->homepageTextGroup(['Register', 'Learn', 'Share & sell', 'Grow'], ['আপনার basic information দিয়ে apply করুন।', 'Training resources দেখে product ও marketing বুঝুন।', 'Selling links share করুন বা manual order নিন।', 'Support, reports এবং wallet দিয়ে business grow করুন।'], 'how_step'),
            'cta_eyebrow' => $this->settings->get('homepage.cta_eyebrow', 'Ready to start?'),
            'cta_title' => $this->settings->get('homepage.cta_title', 'আজই eflix Entrepreneur Platform-এ যুক্ত হন'),
            'cta_text' => $this->settings->get('homepage.cta_text', 'আপনার network ব্যবহার করে online বা offline selling শুরু করুন এবং নিজের business journey তৈরি করুন।'),
            'cta_button' => $this->settings->get('homepage.cta_button', 'Become an Entrepreneur'),
        ];
    }

    private function homepageTextGroup(array $titles, array $texts, string $prefix): array
    {
        $count = $prefix === 'faq_item'
            ? (int) $this->settings->get('homepage.faq_item_count', count($titles))
            : count($titles);
        $count = max(0, min($count, 50));

        if ($count === 0) {
            return [];
        }

        return array_map(fn ($index) => [
            'title' => $this->settings->get("homepage.{$prefix}_title_{$index}", $titles[$index]),
            'text' => $this->settings->get("homepage.{$prefix}_text_{$index}", $texts[$index]),
        ], range(0, $count - 1));
    }

    private function systemSettings(): array
    {
        return [
            'brand_color' => $this->settings->get('system.brand_color', '#f97316'),
            'site_name' => $this->settings->get('system.site_name', config('app.name')),
            'slogan' => $this->settings->get('system.slogan', 'আপনার উদ্যোগ, আমাদের প্ল্যাটফর্ম'),
            'site_url' => $this->settings->get('system.site_url', config('app.url')),
            'phone' => $this->settings->get('system.phone', ''),
            'email' => $this->settings->get('system.email', ''),
            'whatsapp' => $this->settings->get('system.whatsapp', ''),
            'address' => $this->settings->get('system.address', ''),
            'logo' => $this->settings->get('system.logo'),
            'logo_url' => ($logo = $this->settings->get('system.logo'))
                && \Illuminate\Support\Facades\Storage::disk('media')->exists($logo)
                ? asset('storage/media/'.$logo)
                : null,
            'website_icon' => $this->settings->get('system.website_icon'),
            'website_icon_url' => ($icon = $this->settings->get('system.website_icon'))
                && \Illuminate\Support\Facades\Storage::disk('media')->exists($icon)
                ? asset('storage/media/'.$icon)
                : asset('icons/icon.svg'),
        ];
    }

    private function footerSettings(): array
    {
        return [
            'enabled' => (bool) filter_var($this->settings->get('footer.enabled', true), FILTER_VALIDATE_BOOLEAN),
            'description' => $this->settings->get('footer.description', 'Your trusted destination for quality products, fair prices and dependable delivery.'),
            'copyright' => $this->settings->get('footer.copyright', 'All rights reserved.'),
            'newsletter_title' => $this->settings->get('footer.newsletter_title', 'Stay in the loop'),
            'newsletter_text' => $this->settings->get('footer.newsletter_text', 'Get updates about new products and special offers.'),
            'facebook_url' => $this->settings->get('footer.facebook_url', ''),
            'instagram_url' => $this->settings->get('footer.instagram_url', ''),
            'youtube_url' => $this->settings->get('footer.youtube_url', ''),
            'column_one_title' => $this->settings->get('footer.column_one_title', 'Quick links'),
            'column_two_title' => $this->settings->get('footer.column_two_title', 'Customer care'),
            'column_one_links' => $this->footerLinkSettings('one'),
            'column_two_links' => $this->footerLinkSettings('two'),
        ];
    }

    private function footerLinkSettings(string $column): array
    {
        $links = [];
        foreach (range(1, 3) as $index) {
            $links[] = [
                'label' => $this->settings->get("footer.column_{$column}_link_{$index}_label", ''),
                'url' => $this->settings->get("footer.column_{$column}_link_{$index}_url", ''),
            ];
        }

        return $links;
    }

    private function storeHomepageImage(?UploadedFile $file, string $fallbackKey): ?string
    {
        if (! $file) {
            return $this->settings->get($fallbackKey);
        }

        $path = $file->store('homepage', 'media');

        if ($path === false) {
            throw new \RuntimeException('The homepage image could not be stored.');
        }

        return $path;
    }

    public function audit()
    {
        return view('admin.modules.audit', [
            'logs' => AuditLog::query()->with('user')->latest('created_at')->paginate(25),
        ]);
    }

    public function brands()
    {
        return view('admin.modules.brands', ['brands' => Brand::query()->withCount('products')->latest('id')->paginate(20)]);
    }

    public function attributes()
    {
        return view('admin.modules.attributes', ['attributes' => Attribute::query()->withCount('values')->orderBy('name')->paginate(20)]);
    }

    public function coupons()
    {
        return view('admin.modules.coupons', ['coupons' => Coupon::query()->latest('id')->paginate(20)]);
    }

    public function payments()
    {
        return view('admin.modules.payments', ['transactions' => PaymentTransaction::query()->with('order')->latest('id')->paginate(20)]);
    }

    public function restocks()
    {
        return view('admin.modules.restocks', ['requests' => RestockRequest::query()->with('inventory.product', 'requestedBy')->latest('id')->paginate(20)]);
    }

    public function reports(Request $request)
    {
        $period = $request->integer('period', 30);
        $period = in_array($period, [7, 30, 90, 365], true) ? $period : 30;
        $from = now()->subDays($period - 1)->startOfDay();
        $validStatuses = ['cancelled', 'failed', 'refunded'];
        $ordersQuery = Order::query()->whereNotIn('status', $validStatuses)->where('placed_at', '>=', $from);
        $paidQuery = PaymentTransaction::query()->whereIn('status', ['paid', 'partially_refunded'])->where('created_at', '>=', $from);
        $topProducts = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as units, SUM(line_total) as revenue')
            ->whereHas('order', fn ($query) => $query->whereNotIn('status', $validStatuses)->where('placed_at', '>=', $from))
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->limit(10)
            ->get();

        return view('admin.modules.reports', [
            'period' => $period,
            'from' => $from,
            'orders' => $ordersQuery->count(),
            'revenue' => $ordersQuery->sum('grand_total'),
            'paid' => $paidQuery->sum('amount'),
            'customers' => User::query()->whereHas('orders', fn ($query) => $query->where('placed_at', '>=', $from))->count(),
            'topStatuses' => (clone $ordersQuery)->selectRaw('status, count(*) as total')->groupBy('status')->orderByDesc('total')->get(),
            'topProducts' => $topProducts,
        ]);
    }
}

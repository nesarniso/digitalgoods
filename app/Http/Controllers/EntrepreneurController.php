<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Category;
use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Entrepreneurs\Services\ManualOrderService;
use App\Domain\Finance\Services\WalletService;
use App\Domain\Finance\Services\ProfitService;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EntrepreneurController extends Controller
{
    public function register(Request $request)
    {
        if ($this->activeEntrepreneur($request)) {
            return redirect()->route('entrepreneur.dashboard');
        }

        return view('entrepreneur.register', [
            'referralCode' => strtoupper((string) $request->query('ref')),
            'districts' => config('shipping.bangladesh_districts', []),
            'divisions' => config('shipping.bangladesh_divisions', []),
            'upazilas' => config('shipping.bangladesh_upazilas', []),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['required', 'string', 'max:1000'],
            'division' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'upazila' => ['required', 'string', 'max:100'],
            'referral_code' => ['nullable', 'string', 'max:32'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $referrer = ! empty($data['referral_code'])
                ? EntrepreneurProfile::query()->where('entrepreneur_code', strtoupper($data['referral_code']))->first()
                : null;

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);
            // Fresh cPanel installs may have migrations but miss the role
            // seeder; make public applications independent of that seed step.
            Role::findOrCreate('entrepreneur', 'web');
            $user->assignRole('entrepreneur');

            EntrepreneurProfile::create([
                'user_id' => $user->id,
                'entrepreneur_code' => $this->generateCode(),
                'status' => 'pending',
                'address' => $data['address'],
                'division' => $data['division'],
                'district' => $data['district'],
                'upazila' => $data['upazila'],
                'referred_by_user_id' => $referrer?->user_id,
            ]);

            return $user;
        });

        return redirect()->route('entrepreneur.login')->with('success', 'Application submitted. You can sign in after approval.');
    }

    public function login()
    {
        if (auth()->check() && auth()->user()->hasRole('entrepreneur') && auth()->user()->entrepreneurProfile?->status === 'active') {
            return redirect()->route('entrepreneur.dashboard');
        }

        return view('entrepreneur.login');
    }

    private function activeEntrepreneur(Request $request): bool
    {
        return $request->user()?->hasRole('entrepreneur') === true
            && $request->user()->entrepreneurProfile?->status === 'active';
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['identifier'])
            ->whereHas('entrepreneurProfile', fn ($query) => $query->where('status', 'active'))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['identifier' => 'These credentials do not match an active entrepreneur account.']);
        }

        auth()->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('entrepreneur.dashboard');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $user->load('entrepreneurProfile');

        return view('entrepreneur.dashboard', [
            'profile' => $user->entrepreneurProfile,
            'products' => $user->entrepreneurProducts()->with(['product.category', 'product.images'])->where('status', 'active')->latest()->paginate(12),
            'balance' => app(WalletService::class)->balance($user),
            'sales' => Order::query()->where('entrepreneur_id', $user->id)->with(['items', 'user'])->latest()->paginate(10, ['*'], 'sales_page'),
            'withdrawals' => $user->withdrawals()->latest()->limit(5)->get(),
            'salesCount' => Order::query()->where('entrepreneur_id', $user->id)->count(),
            'deliveredSalesCount' => Order::query()->where('entrepreneur_id', $user->id)->where('status', 'delivered')->count(),
            'customersCount' => User::query()
                ->whereHas('orders', fn ($query) => $query->where('entrepreneur_id', $user->id))
                ->count(),
            'teamCount' => EntrepreneurProfile::query()->where('referred_by_user_id', $user->id)->count(),
            'referralLink' => route('entrepreneur.referral-link', $user->entrepreneurProfile->entrepreneur_code),
            'recentOrders' => Order::query()
                ->where('entrepreneur_id', $user->id)
                ->with('user')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function requestWithdrawal(Request $request, WalletService $wallet)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'in:bkash,nagad,bank'],
            'account_number' => ['required', 'string', 'max:100'],
        ]);

        $wallet->requestWithdrawal($request->user(), (string) $data['amount'], $data['method'], $data['account_number']);

        return back()->with('success', 'Withdrawal request submitted for admin review.');
    }

    public function sales(Request $request)
    {
        $sales = Order::query()
            ->where('entrepreneur_id', $request->user()->id)
            ->with(['items', 'user'])
            ->latest()
            ->paginate(20);

        return view('entrepreneur.sales', compact('sales'));
    }

    public function products(Request $request, ProfitService $profits)
    {
        $sort = $request->string('sort')->toString();
        $productsQuery = $request->user()->entrepreneurProducts()
            ->with(['product.category', 'product.images'])
            ->where('entrepreneur_products.status', 'active')
            ->whereHas('product', function ($query) use ($request) {
                $query->when($request->filled('q'), fn ($product) => $product->where(function ($search) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $search->where('name', 'like', $term)->orWhere('sku', 'like', $term);
                }));
                $query->when($request->filled('category'), fn ($product) => $product->whereHas('category', fn ($category) => $category->where('slug', $request->string('category'))));
            });

        if ($sort === 'price_low') {
            $productsQuery->join('products', 'products.id', '=', 'entrepreneur_products.product_id')->orderBy('products.regular_price')->select('entrepreneur_products.*');
        } elseif ($sort === 'price_high') {
            $productsQuery->join('products', 'products.id', '=', 'entrepreneur_products.product_id')->orderByDesc('products.regular_price')->select('entrepreneur_products.*');
        } else {
            $productsQuery->latest('entrepreneur_products.created_at');
        }

        $products = $productsQuery
            ->paginate(18)
            ->withQueryString();
        $productIds = $products->getCollection()->pluck('product_id');
        $clicks = DB::table('entrepreneur_link_clicks')
            ->where('entrepreneur_id', $request->user()->id)
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('count(*) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id');
        $purchases = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.entrepreneur_id', $request->user()->id)
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->whereIn('order_items.product_id', $productIds)
            ->select('order_items.product_id', DB::raw('count(distinct orders.id) as total'))
            ->groupBy('order_items.product_id')
            ->pluck('total', 'product_id');
        $products->getCollection()->each(function ($assignment) use ($clicks, $purchases) {
            $assignment->link_clicks_count = (int) ($clicks[$assignment->product_id] ?? 0);
            $assignment->purchases_count = (int) ($purchases[$assignment->product_id] ?? 0);
        });
        $products->getCollection()->each(function ($assignment) use ($profits) {
            $product = $assignment->product;
            $assignment->estimated_profit = $product?->purchase_price === null
                ? '0.00'
                : $profits->entrepreneurProfit((string) $product->purchase_price, (string) $product->effective_price, '0', $profits->percentageForProduct($product), $profits->flatRateForProduct($product));
        });

        return view('entrepreneur.products', [
            'products' => $products,
            'categories' => Category::query()->whereHas('products', fn ($query) => $query->whereHas('entrepreneurProducts', fn ($assignment) => $assignment->where('entrepreneur_id', $request->user()->id)->where('status', 'active')))->orderBy('name')->get(),
            'totalClicks' => DB::table('entrepreneur_link_clicks')->where('entrepreneur_id', $request->user()->id)->count(),
            'totalPurchases' => Order::query()->where('entrepreneur_id', $request->user()->id)->whereNotIn('status', ['cancelled', 'refunded'])->count(),
        ]);
    }

    public function withdrawals(Request $request, WalletService $wallet)
    {
        return view('entrepreneur.withdrawals', [
            'balance' => $wallet->balance($request->user()),
            'withdrawals' => $request->user()->withdrawals()->latest()->paginate(20),
        ]);
    }

    public function customers(Request $request)
    {
        $customers = User::query()
            ->whereHas('orders', fn ($query) => $query->where('entrepreneur_id', $request->user()->id))
            ->withCount(['orders as entrepreneur_orders_count' => fn ($query) => $query->where('entrepreneur_id', $request->user()->id)])
            ->latest('id')
            ->paginate(20);

        return view('entrepreneur.customers', compact('customers'));
    }

    public function customerOrders(Request $request, User $customer)
    {
        $orders = Order::query()
            ->where('entrepreneur_id', $request->user()->id)
            ->where('user_id', $customer->id)
            ->with('items')
            ->latest()
            ->paginate(20);

        abort_unless($orders->total() > 0, 404);

        return view('entrepreneur.customer-orders', compact('customer', 'orders'));
    }

    public function orderDetail(Request $request, Order $order)
    {
        abort_unless((int) $order->entrepreneur_id === (int) $request->user()->id, 404);
        $order->load(['user', 'items']);

        return view('entrepreneur.order-detail', compact('order'));
    }

    public function marketing(Request $request, ProfitService $profits)
    {
        $user = $request->user()->load('entrepreneurProfile');
        $profile = $user->entrepreneurProfile;
        $products = $user->entrepreneurProducts()
            ->with(['product.category', 'product.images'])
            ->where('status', 'active')
            ->latest()
            ->get();
        $products->each(function ($assignment) use ($profits) {
            $product = $assignment->product;
            $assignment->estimated_profit = $product?->purchase_price === null
                ? '0.00'
                : $profits->entrepreneurProfit(
                    (string) $product->purchase_price,
                    (string) $product->effective_price,
                    '0',
                    $profits->percentageForProduct($product),
                    $profits->flatRateForProduct($product),
                );
        });

        return view('entrepreneur.marketing', [
            'profile' => $profile,
            'products' => $products,
            'referralCount' => EntrepreneurProfile::query()->where('referred_by_user_id', $user->id)->count(),
            'referralLink' => route('entrepreneur.referral-link', $profile->entrepreneur_code),
            'totalOrders' => Order::query()->where('entrepreneur_id', $user->id)->count(),
            'deliveredOrders' => Order::query()->where('entrepreneur_id', $user->id)->where('status', 'delivered')->count(),
            'pendingProfit' => OrderItem::query()->whereHas('order', fn ($query) => $query->where('entrepreneur_id', $user->id))->where('profit_status', 'pending')->sum('profit'),
            'availableProfit' => app(WalletService::class)->balance($user),
        ]);
    }

    public function support(Request $request)
    {
        return view('entrepreneur.support', [
            'tickets' => $request->user()->supportTickets()->latest()->get(),
        ]);
    }

    public function team(Request $request)
    {
        $user = $request->user()->load('entrepreneurProfile');
        $referrals = EntrepreneurProfile::query()
            ->where('referred_by_user_id', $user->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('entrepreneur.team', [
            'profile' => $user->entrepreneurProfile,
            'referrals' => $referrals,
            'activeCount' => EntrepreneurProfile::query()->where('referred_by_user_id', $user->id)->where('status', 'active')->count(),
            'totalCount' => $referrals->total(),
            'referralLink' => route('entrepreneur.referral-link', $user->entrepreneurProfile->entrepreneur_code),
        ]);
    }

    public function finance(Request $request, WalletService $wallet)
    {
        $user = $request->user();

        return view('entrepreneur.finance', [
            'balance' => $wallet->balance($user),
            'transactions' => $user->walletTransactions()->latest()->paginate(20, ['*'], 'transactions_page'),
            'withdrawals' => $user->withdrawals()->latest()->paginate(10, ['*'], 'withdrawals_page'),
        ]);
    }

    public function reports(Request $request)
    {
        $entrepreneurId = $request->user()->id;
        $orders = Order::query()->where('entrepreneur_id', $entrepreneurId);
        $statusCounts = (clone $orders)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $profit = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('entrepreneur_id', $entrepreneurId))
            ->whereIn('profit_status', ['pending', 'available'])
            ->sum('profit');
        $topProducts = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as units, SUM(line_total) as revenue, SUM(profit) as profit')
            ->whereHas('order', fn ($query) => $query->where('entrepreneur_id', $entrepreneurId))
            ->groupBy('product_name')
            ->orderByDesc('units')
            ->limit(10)
            ->get();

        return view('entrepreneur.reports', [
            'totalOrders' => (clone $orders)->count(),
            'deliveredOrders' => (clone $orders)->where('status', 'delivered')->count(),
            'totalRevenue' => (clone $orders)->sum('grand_total'),
            'deliveredRevenue' => (clone $orders)->where('status', 'delivered')->sum('grand_total'),
            'statusCounts' => $statusCounts,
            'profit' => $profit,
            'topProducts' => $topProducts,
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('entrepreneurProfile');

        return view('entrepreneur.profile', ['user' => $user, 'profile' => $user->entrepreneurProfile]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone,'.$user->id],
            'address' => ['required', 'string', 'max:1000'],
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'upazila' => ['nullable', 'string', 'max:100'],
            'union_or_ward' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update(collect($data)->only(['name', 'email', 'phone'])->all());
            $user->entrepreneurProfile()->updateOrCreate([], collect($data)->only(['address', 'division', 'district', 'upazila', 'union_or_ward'])->all());
        });

        return back()->with('success', 'Profile details updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function storeSupport(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:Order support,Payment,Returns and refunds,Product question,General inquiry'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $request->user()->supportTickets()->create($data);

        return back()->with('success', 'Your support ticket has been submitted.');
    }

    public function manualOrder(Request $request)
    {
        return view('entrepreneur.manual-order', [
            'products' => Product::query()
                ->where('status', 'published')
                ->whereHas('entrepreneurProducts', fn ($query) => $query
                    ->where('entrepreneur_id', $request->user()->id)
                    ->where('status', 'active'))
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'purchase_price', 'regular_price', 'sale_price']),
        ]);
    }

    public function storeManualOrder(Request $request, ManualOrderService $orders)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'selling_price' => ['required', 'numeric', 'min:0.01'],
        ]);

        $order = $orders->create($request->user(), $data);

        return redirect()->route('entrepreneur.dashboard')->with('success', "Manual order {$order->order_number} created.");
    }

    public function addProduct(Request $request, Product $product)
    {
        abort_unless($product->status === 'published', 404);

        EntrepreneurProduct::query()->updateOrCreate(
            ['entrepreneur_id' => $request->user()->id, 'product_id' => $product->id],
            ['status' => 'active']
        );

        return back()->with('success', 'Product added to your marketing list.');
    }

    private function generateCode(): string
    {
        do {
            $code = 'ENT'.random_int(10000, 99999);
        } while (EntrepreneurProfile::query()->where('entrepreneur_code', $code)->exists());

        return $code;
    }
}

<?php

namespace App\Http\Controllers;

use Carbon\CarbonPeriod;
use App\Domain\Catalog\Models\Product;
use App\Domain\Analytics\Models\AnalyticsEvent;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function abandonedCarts()
    {
        return view('admin.abandoned-carts', [
            'abandonedCarts' => Cart::query()
                ->whereNotNull('checkout_email')
                ->whereHas('items')
                ->withCount('items')
                ->latest('checkout_contacted_at')
                ->paginate(25),
        ]);
    }

    public function __invoke()
    {
        $period = (int) request()->integer('analytics_period', 30);
        $period = in_array($period, [1, 7, 30, 90], true) ? $period : 30;
        $analyticsStart = now()->subDays($period - 1)->startOfDay();
        $analyticsQuery = AnalyticsEvent::query()->where('occurred_at', '>=', $analyticsStart);
        $uniqueVisitors = (clone $analyticsQuery)->distinct('visitor_id')->count('visitor_id');
        $pageViews = (clone $analyticsQuery)->where('event_type', 'page_view')->count();
        $productViews = (clone $analyticsQuery)->where('event_type', 'product_view')->count();
        $topViewedProducts = (clone $analyticsQuery)
            ->where('event_type', 'product_view')
            ->whereNotNull('product_id')
            ->selectRaw('product_id, COUNT(*) as views, COUNT(DISTINCT visitor_id) as unique_visitors')
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit(10)
            ->with('product:id,name')
            ->get();

        $periodStart = now()->subDays(29)->startOfDay();
        $analyticsOrders = Order::query()
            ->where('placed_at', '>=', $periodStart)
            ->whereNotIn('status', ['cancelled', 'failed', 'refunded'])
            ->get(['status', 'grand_total', 'placed_at']);
        $dailySales = collect(CarbonPeriod::create($periodStart, now()->startOfDay()))
            ->mapWithKeys(fn ($date) => [$date->toDateString() => 0.0]);
        foreach ($analyticsOrders as $order) {
            $day = $order->placed_at?->toDateString();
            if ($day !== null && $dailySales->has($day)) {
                $dailySales[$day] += (float) $order->grand_total;
            }
        }
        $topProducts = OrderItem::query()
            ->whereHas('order', fn ($query) => $query
                ->where('placed_at', '>=', $periodStart)
                ->whereNotIn('status', ['cancelled', 'failed', 'refunded']))
            ->with('product:id,name')
            ->get(['product_id', 'quantity', 'line_total'])
            ->groupBy('product_id')
            ->map(fn ($items) => [
                'name' => $items->first()->product?->name ?? 'Deleted product',
                'quantity' => (int) $items->sum('quantity'),
                'sales' => (float) $items->sum(fn ($item) => (float) $item->line_total),
            ])
            ->sortByDesc('sales')
            ->take(5)
            ->values();

        $completedStatuses = ['delivered', 'completed'];
        $profitRows = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->whereIn('status', $completedStatuses))
            ->selectRaw('COALESCE(SUM(line_total), 0) as sales_total')
            ->selectRaw('COALESCE(SUM(supplier_cost), 0) as purchase_total')
            ->first();
        $salesTotal = (float) $profitRows->sales_total;
        $purchaseTotal = (float) $profitRows->purchase_total;
        $realizedProfit = $salesTotal - $purchaseTotal;

        return view('admin.dashboard', [
            'metrics' => [
                'revenue' => Order::query()->whereNotIn('status', ['cancelled', 'failed', 'refunded'])->sum('grand_total'),
                'orders' => Order::query()->count(),
                'customers' => User::query()->count(),
                'products' => Product::query()->where('status', 'published')->count(),
                'lowStock' => 0,
            ],
            'profitSummary' => [
                'sales' => $salesTotal,
                'purchaseCost' => $purchaseTotal,
                'profit' => $realizedProfit,
                'margin' => $salesTotal > 0 ? ($realizedProfit / $salesTotal) * 100 : 0,
            ],
            'recentOrders' => Order::query()->latest('placed_at')->limit(8)->get(),
            'abandonedCarts' => Cart::query()
                ->whereNotNull('checkout_email')
                ->whereHas('items')
                ->withCount('items')
                ->latest('checkout_contacted_at')
                ->limit(8)
                ->get(),
            'salesAnalytics' => [
                'periodLabel' => $periodStart->format('d M').' - '.now()->format('d M Y'),
                'orders' => $analyticsOrders->count(),
                'revenue' => (float) $analyticsOrders->sum(fn ($order) => (float) $order->grand_total),
                'averageOrderValue' => $analyticsOrders->isNotEmpty()
                    ? (float) $analyticsOrders->avg(fn ($order) => (float) $order->grand_total)
                    : 0,
                'dailySales' => $dailySales,
                'topProducts' => $topProducts,
            ],
            'visitorAnalytics' => [
                'period' => $period,
                'periodLabel' => $analyticsStart->format('d M').' - '.now()->format('d M Y'),
                'uniqueVisitors' => $uniqueVisitors,
                'pageViews' => $pageViews,
                'productViews' => $productViews,
                'topProducts' => $topViewedProducts,
            ],
        ]);
    }
}

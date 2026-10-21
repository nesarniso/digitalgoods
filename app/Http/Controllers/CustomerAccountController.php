<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Orders\Services\ReturnRequestService;
use App\Domain\Catalog\Models\ProductReview;
use App\Http\Requests\Storefront\StoreReturnRequest;
use App\Domain\Users\Models\Address;
use App\Domain\Users\Services\AddressService;
use App\Http\Requests\Storefront\StoreAddressRequest;
use App\Http\Requests\Storefront\UpdateAddressRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Domain\Commerce\Models\Coupon;

class CustomerAccountController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly AddressService $addresses,
        private readonly ReturnRequestService $returns,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('items.product')->latest('id')->paginate(10);
        $orderItems = $orders->getCollection()->flatMap(fn (Order $order) => $order->items);
        $reviewedPurchases = ProductReview::query()
            ->where('user_id', $user->id)
            ->whereIn('order_id', $orders->getCollection()->pluck('id'))
            ->whereIn('product_id', $orderItems->pluck('product_id')->filter()->unique())
            ->get(['order_id', 'product_id', 'status'])
            ->keyBy(fn (ProductReview $review): string => $review->order_id.':'.$review->product_id);

        return view('storefront.account.index', [
            'orders' => $orders,
            'reviewedPurchases' => $reviewedPurchases,
            'digitalLicenses' => $user->orders()
                ->whereIn('status', ['confirmed', 'processing', 'delivered'])
                ->with(['digitalLicenses' => fn ($query) => $query->where('status', 'assigned')->with('product')])
                ->get()
                ->flatMap(fn ($order) => $order->isPaid() ? $order->digitalLicenses : collect())
                ->map(fn ($license) => [
                    'product_name' => $license->product?->name ?? 'Digital product',
                    'reference' => $license->reference,
                    'secret' => $license->plain_secret,
                    'instructions' => $license->instructions,
                ]),
            'addresses' => $user->addresses()->latest('is_default')->latest('id')->get(),
            'unreadNotifications' => $user->unreadNotifications()->count(),
            'orderStats' => [
                'total' => $user->orders()->count(),
                'pending' => $user->orders()->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
                'delivered' => $user->orders()->where('status', 'delivered')->count(),
                'returns' => $user->orders()->whereHas('returnRequests')->count(),
                'wishlist' => $user->wishlistItems()->count(),
            ],
            'loyaltyPoints' => (int) ($user->customerProfile?->loyalty_points ?? 0),
        ]);
    }

    public function coupons()
    {
        return view('storefront.account.coupons', [
            'coupons' => Coupon::query()->where('is_active', true)->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })->latest()->get(),
        ]);
    }

    public function profile()
    {
        return view('storefront.account.profile');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);
        $request->user()->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function requestEmailChange(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user())],
        ]);
        $code = (string) random_int(100000, 999999);
        $request->session()->put('email_change', [
            'email' => strtolower($data['email']),
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        Mail::raw("Your ".config('app.name')." email change confirmation code is {$code}. It expires in 10 minutes.", function ($message) use ($data): void {
            $message->to($data['email'])->subject('Confirm your email change');
        });

        return back()->with('success', 'A confirmation code was sent to your new email address.');
    }

    public function confirmEmailChange(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $pending = $request->session()->get('email_change');

        if (! is_array($pending) || now()->timestamp > ($pending['expires_at'] ?? 0) || ! Hash::check($data['code'], $pending['code'] ?? '')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => ['The confirmation code is invalid or expired.'],
            ]);
        }

        $request->user()->update(['email' => $pending['email']]);
        $request->session()->forget('email_change');

        return back()->with('success', 'Email address updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function storeAddress(StoreAddressRequest $request)
    {
        $this->addresses->create($request->user(), $request->validated());

        return back()->with('success', 'Address added.');
    }

    public function editAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        return view('storefront.account.index', [
            'orders' => $request->user()->orders()->latest('id')->paginate(10),
            'addresses' => $request->user()->addresses()->latest('is_default')->latest('id')->get(),
            'editingAddress' => $address,
        ]);
    }

    public function updateAddress(UpdateAddressRequest $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $this->addresses->update($address, $request->validated());

        return redirect()->route('account.index')->with('success', 'Address updated.');
    }

    public function setDefaultAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $this->addresses->update($address, ['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    public function deleteAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $this->addresses->delete($address);

        return back()->with('success', 'Address deleted.');
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $with = ['items.product', 'items.variant.attributeValues.attribute', 'items.returnRequests', 'returnRequests.item', 'shippingAddress', 'statusHistory', 'latestPaymentTransaction'];
        if (Schema::hasTable('shipments')) {
            $with[] = 'latestShipment';
        }

        $order->load($with);
        $digitalLicenses = $order->isPaid()
            ? $order->digitalLicenses()
                ->where('status', 'assigned')
                ->with('product')
                ->get()
                ->map(fn ($license) => [
                    'product_name' => $license->product?->name ?? 'Digital product',
                    'reference' => $license->reference,
                    'secret' => $license->plain_secret,
                    'instructions' => $license->instructions,
                ])
            : collect();

        return view('storefront.account.order', [
            'order' => $order,
            'digitalLicenses' => $digitalLicenses,
        ]);
    }

    public function requestReturn(StoreReturnRequest $request, Order $order, int $item)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $orderItem = $order->items()->findOrFail($item);
        $this->returns->create(
            $request->user(),
            $order,
            $orderItem,
            $request->integer('quantity'),
            $request->string('reason')->toString(),
            $request->input('customer_note'),
        );

        return back()->with('success', 'Return request submitted.');
    }

}

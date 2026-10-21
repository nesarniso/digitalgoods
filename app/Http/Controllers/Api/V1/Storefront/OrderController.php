<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Commerce\Services\CartService;
use App\Domain\Entrepreneurs\Services\AttributionService;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Users\Models\Address;
use App\Domain\Auth\Services\AuthService;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Http\Resources\Orders\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CartService $cartService,
        private readonly AttributionService $attribution,
        private readonly AuthService $auth,
    ) {}

    public function store(PlaceOrderRequest $request)
    {
        $user = $request->user('sanctum') ?: $request->user();
        $accountToken = null;
        if (! $user) {
            $email = strtolower(trim($request->string('guest_email')->toString()));
            $existingUser = User::query()->where('email', $email)->first();
            $user = $existingUser
                ? $this->auth->attemptPasswordLogin($email, $request->string('password')->toString(), $request)
                : $this->auth->registerCustomer([
                    'name' => $request->string('name')->toString(),
                    'email' => $email,
                    'phone' => $request->string('phone')->toString(),
                    'password' => $request->string('password')->toString(),
                ]);
            $this->cartService->mergeGuestCartIntoUser($request->header('X-Guest-Cart-Token'), $user);
            $accountToken = $user->createToken('checkout-session')->plainTextToken;
        }
        $result = $this->cartService->resolveCart($user, $request->header('X-Guest-Cart-Token'));
        $cart = $result['cart'];

        $digitalOnly = $this->cartService->isDigitalOnly($cart);
        $address = $this->resolveAddress($request, $user);
        $shippingMethod = $digitalOnly ? null : ($request->filled('shipping_method_id')
            ? ShippingMethod::findOrFail($request->integer('shipping_method_id'))
            : ShippingMethod::query()->where('is_active', true)->orderBy('sort_order')->firstOrFail());

        $order = $this->orders->placeOrder(
            $cart,
            $address,
            $shippingMethod,
            $request->string('payment_method'),
            $request->string('idempotency_key'),
            $user,
            null,
            null,
            $request->input('notes'),
            $this->attribution->forOrder($request, $request->input('attribution_code')),
            $request->input('payer_mobile'),
            $request->input('transaction_id'),
            $request->input('paid_at'),
            $request->input('submitted_amount'),
        );

        $response = response()->json([
            'success' => true,
            'message' => 'Order placed.',
            'data' => new OrderResource($order),
            'meta' => (object) [],
        ], 201);

        if ($result['guest_token']) {
            $response->header('X-Guest-Cart-Token', $result['guest_token']);
        }
        if ($accountToken) {
            $response->setData(array_merge($response->getData(true), ['account_token' => $accountToken]));
        }

        return $response;
    }

    public function accountStatus(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => User::query()->where('email', strtolower(trim($data['email'])))->exists(),
            ],
        ]);
    }

    public function accountLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->auth->attemptPasswordLogin($data['email'], $data['password'], $request);
        $this->cartService->mergeGuestCartIntoUser($request->header('X-Guest-Cart-Token'), $user);
        $token = $user->createToken('checkout-session')->plainTextToken;
        $address = $user->addresses()->orderByDesc('is_default')->latest('id')->first();

        return response()->json([
            'success' => true,
            'account_token' => $token,
            'data' => [
                'user' => ['name' => $user->name, 'phone' => $user->phone, 'email' => $user->email],
                'address' => $address,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved.',
            'data' => OrderResource::collection($orders),
            'meta' => ['pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404, 'Order not found.');

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved.',
            'data' => new OrderResource($order->load(['items', 'addresses', 'statusHistory'])),
            'meta' => (object) [],
        ]);
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::query()
            ->whereNull('user_id')
            ->where('order_number', $request->string('order_number'))
            ->where('customer_email', $request->string('email'))
            ->with(['items', 'statusHistory'])
            ->first();

        abort_unless($order, 404, 'Order not found.');

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved.',
            'data' => new OrderResource($order),
            'meta' => (object) [],
        ]);
    }


    private function resolveAddress(PlaceOrderRequest $request, $user): Address
    {
        if ($request->filled('address_id')) {
            $address = Address::query()
                ->whereKey($request->integer('address_id'))
                ->where('user_id', $user?->id)
                ->firstOrFail();

            abort_unless($user && $address->user_id === $user->id, 404, 'Address not found.');

            return $address;
        }

        return new Address([
            'name' => $request->string('name'),
            'phone' => $request->string('phone'),
            'country' => $request->input('country', 'Bangladesh'),
            'division' => $request->input('division'),
            'district' => $request->input('district'),
            'upazila' => $request->input('upazila'),
            'area' => $request->input('area'),
            'postal_code' => $request->input('postal_code'),
            'address_line' => $request->string('address_line'),
        ]);
    }
}

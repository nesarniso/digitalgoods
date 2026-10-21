<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Commerce\Models\Coupon;
use App\Domain\Commerce\Services\CouponService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Commerce\StoreCouponRequest;
use App\Http\Requests\Admin\Commerce\UpdateCouponRequest;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $coupons) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('coupon.view'), 403, 'This action is unauthorized.');

        $coupons = Coupon::query()
            ->when($request->query('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Coupons retrieved.',
            'data' => $coupons->items(),
            'meta' => ['pagination' => [
                'current_page' => $coupons->currentPage(),
                'per_page' => $coupons->perPage(),
                'total' => $coupons->total(),
            ]],
        ]);
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = $this->coupons->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Coupon created.',
            'data' => $coupon->load(['products', 'categories']),
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $coupon = $this->coupons->update($coupon, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated.',
            'data' => $coupon->load(['products', 'categories']),
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, Coupon $coupon)
    {
        abort_unless($request->user()->can('coupon.delete'), 403, 'This action is unauthorized.');

        $this->coupons->delete($coupon, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}

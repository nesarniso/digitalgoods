<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Services\ShippingConfigService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shipping\SetShippingRateRequest;
use App\Http\Requests\Admin\Shipping\StoreShippingMethodRequest;
use App\Http\Requests\Admin\Shipping\UpdateShippingMethodRequest;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function __construct(private readonly ShippingConfigService $config) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Shipping methods retrieved.',
            'data' => ShippingMethod::with('rates.zone')->orderBy('sort_order')->get(),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreShippingMethodRequest $request)
    {
        $method = $this->config->createMethod($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipping method created.',
            'data' => $method,
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateShippingMethodRequest $request, ShippingMethod $method)
    {
        $method = $this->config->updateMethod($method, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipping method updated.',
            'data' => $method,
            'meta' => (object) [],
        ]);
    }

    public function setRate(SetShippingRateRequest $request, ShippingMethod $method)
    {
        $rate = $this->config->setRate(
            $method,
            $request->integer('shipping_zone_id') ?: null,
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate set.',
            'data' => $rate,
            'meta' => (object) [],
        ]);
    }
}

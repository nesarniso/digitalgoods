<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Shipping\Models\ShippingZone;
use App\Domain\Shipping\Services\ShippingConfigService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Shipping\StoreShippingZoneRequest;
use App\Http\Requests\Admin\Shipping\UpdateShippingZoneRequest;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function __construct(private readonly ShippingConfigService $config) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Shipping zones retrieved.',
            'data' => ShippingZone::orderBy('name')->get(),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreShippingZoneRequest $request)
    {
        $zone = $this->config->createZone($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone created.',
            'data' => $zone,
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateShippingZoneRequest $request, ShippingZone $zone)
    {
        $zone = $this->config->updateZone($zone, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipping zone updated.',
            'data' => $zone,
            'meta' => (object) [],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Models\ShippingZone;
use App\Domain\Shipping\Services\ShippingConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class AdminShippingController extends Controller
{
    public function __construct(private readonly ShippingConfigService $shipping) {}

    public function methodCreate()
    {
        return view('admin.shipping.method-form', ['method' => new ShippingMethod]);
    }

    public function methodStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:flat,free,weight_based,price_based'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $method = $this->shipping->createMethod($data, $request->user());

        return redirect()->route('admin.shipping')->with('success', $method->name.' created.');
    }

    public function methodEdit(ShippingMethod $method)
    {
        return view('admin.shipping.method-form', [
            'method' => $method->load(['rates.zone']),
            'zones' => ShippingZone::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function methodUpdate(Request $request, ShippingMethod $method)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:flat,free,weight_based,price_based'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $this->shipping->updateMethod($method, $data, $request->user());

        return back()->with('success', 'Shipping method updated.');
    }

    public function methodDestroy(Request $request, ShippingMethod $method)
    {
        $this->shipping->deleteMethod($method, $request->user());

        return redirect()->route('admin.shipping')->with('success', $method->name.' deleted.');
    }

    public function rateStore(Request $request, ShippingMethod $method)
    {
        $data = $request->validate([
            'shipping_zone_id' => ['nullable', 'exists:shipping_zones,id'],
            'base_rate' => ['required', 'numeric', 'min:0'],
            'per_kg_rate' => ['nullable', 'numeric', 'min:0'],
            'free_above_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $this->shipping->setRate($method, $data['shipping_zone_id'] ?? null, $data, $request->user());

        return back()->with('success', 'Shipping rate saved.');
    }

    public function zoneCreate()
    {
        return view('admin/shipping/zone-form', [
            'zone' => new ShippingZone,
            'methods' => collect(),
            'districts' => Config::get('shipping.bangladesh_districts', []),
            'divisions' => Config::get('shipping.bangladesh_divisions', []),
        ]);
    }

    public function zoneStore(Request $request)
    {
        $this->shipping->createZone($this->zoneData($request), $request->user());

        return redirect()->route('admin.shipping')->with('success', 'Shipping zone created.');
    }

    public function zoneEdit(ShippingZone $zone)
    {
        return view('admin/shipping/zone-form', [
            'zone' => $zone,
            'methods' => ShippingMethod::query()->with('rates')->orderBy('sort_order')->orderBy('name')->get(),
            'districts' => Config::get('shipping.bangladesh_districts', []),
            'divisions' => Config::get('shipping.bangladesh_divisions', []),
        ]);
    }

    public function zoneUpdate(Request $request, ShippingZone $zone)
    {
        $this->shipping->updateZone($zone, $this->zoneData($request), $request->user());

        return back()->with('success', 'Shipping zone updated.');
    }

    public function zoneDestroy(Request $request, ShippingZone $zone)
    {
        $this->shipping->deleteZone($zone, $request->user());

        return redirect()->route('admin.shipping')->with('success', $zone->name.' deleted.');
    }

    private function zoneData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable'],
            'district.*' => ['string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $districts = $request->input('district', []);
        $data['district'] = is_array($districts)
            ? implode(', ', array_values(array_filter(array_map('trim', $districts))))
            : trim((string) $districts);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}

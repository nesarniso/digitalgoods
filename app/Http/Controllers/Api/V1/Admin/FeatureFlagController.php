<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Settings\Services\FeatureFlagService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function __construct(private readonly FeatureFlagService $flags) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Feature flags retrieved.',
            'data' => $this->flags->all(),
            'meta' => (object) [],
        ]);
    }

    public function enable(Request $request, string $key)
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');
        $this->guardMultiVendorToggle($request, $key);

        $flag = $this->flags->enable($key, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Feature '{$key}' enabled.",
            'data' => $flag,
            'meta' => (object) [],
        ]);
    }

    public function disable(Request $request, string $key)
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');

        $flag = $this->flags->disable($key, $request->user());

        return response()->json([
            'success' => true,
            'message' => "Feature '{$key}' disabled.",
            'data' => $flag,
            'meta' => (object) [],
        ]);
    }

    /**
     * Per the Phase 0 architecture note: flipping multi_vendor on isn't a
     * casual toggle — it touches product/order/commission schemas that
     * don't fully exist until Phase 14. Require an explicit confirmation
     * flag so it can't be enabled by accident before that phase lands.
     */
    private function guardMultiVendorToggle(Request $request, string $key): void
    {
        if ($key !== 'multi_vendor') {
            return;
        }

        abort_unless(
            $request->boolean('confirm_vendor_schema_ready'),
            409,
            'Enabling multi_vendor requires confirm_vendor_schema_ready=true — the vendor marketplace schema (Phase 14) must be in place first.'
        );
    }
}

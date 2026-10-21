<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Settings\Services\SettingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(Request $request)
    {
        $this->authorizeAction($request);

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved.',
            'data' => $this->settings->all(),
            'meta' => (object) [],
        ]);
    }

    public function update(UpdateSettingRequest $request, string $key)
    {
        $setting = $this->settings->set(
            key: $key,
            value: $request->input('value'),
            group: $request->input('group', 'general'),
            type: $request->input('type', 'string'),
            actor: $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting updated.',
            'data' => $setting,
            'meta' => (object) [],
        ]);
    }

    private function authorizeAction(Request $request): void
    {
        abort_unless($request->user()->can('setting.manage'), 403, 'This action is unauthorized.');
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Audit\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('report.view'), 403, 'This action is unauthorized.');

        $query = AuditLog::query()->with('user:id,name,email')->latest('created_at');

        if ($module = $request->query('module')) {
            $query->where('module', $module);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved.',
            'data' => $logs->items(),
            'meta' => ['pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]],
        ]);
    }
}

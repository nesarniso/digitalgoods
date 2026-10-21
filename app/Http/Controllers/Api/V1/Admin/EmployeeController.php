<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Users\Services\EmployeeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRoleRequest;
use App\Http\Resources\Admin\EmployeeResource;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('employee.view'), 403, 'This action is unauthorized.');

        $employees = User::query()
            ->whereHas('roles', fn ($q) => $q->whereNotIn('name', ['customer']))
            ->with(['roles', 'employeeProfile'])
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Employees retrieved.',
            'data' => EmployeeResource::collection($employees),
            'meta' => ['pagination' => [
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]],
        ]);
    }

    public function store(CreateEmployeeRequest $request)
    {
        $employee = $this->employees->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Employee created.',
            'data' => new EmployeeResource($employee->load(['roles', 'employeeProfile'])),
            'meta' => (object) [],
        ], 201);
    }

    public function updateRole(UpdateEmployeeRoleRequest $request, User $employee)
    {
        $this->employees->updateRole($employee, $request->string('role'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Employee role updated.',
            'data' => new EmployeeResource($employee->load(['roles', 'employeeProfile'])),
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, User $employee)
    {
        abort_unless($request->user()->can('employee.manage'), 403, 'This action is unauthorized.');

        $this->employees->deactivate($employee, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Employee deactivated.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}

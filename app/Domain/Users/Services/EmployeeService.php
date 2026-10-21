<?php

namespace App\Domain\Users\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Users\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates and manages staff accounts (Admin, Manager, Product Manager,
 * Order Manager, Inventory Manager, Marketing Manager, Support Agent,
 * Accountant — spec section 4). Only a Super Admin should reach this
 * service in practice; that's enforced at the controller/route level via
 * the `employee.manage` permission, not here.
 */
class EmployeeService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            EmployeeProfile::create([
                'user_id' => $user->id,
                'employee_id' => $this->generateEmployeeId(),
                'department' => $data['department'] ?? null,
                'designation' => $data['designation'] ?? null,
                'reports_to_user_id' => $data['reports_to_user_id'] ?? null,
            ]);

            $this->auditLogger->log(
                actor: $actor,
                action: 'created',
                module: 'Employees',
                entityType: 'User',
                entityId: (string) $user->id,
                newValue: ['name' => $user->name, 'email' => $user->email, 'role' => $data['role']],
            );

            return $user;
        });
    }

    public function updateRole(User $employee, string $role, User $actor): User
    {
        $previousRoles = $employee->getRoleNames()->toArray();

        $employee->syncRoles([$role]);

        $this->auditLogger->log(
            actor: $actor,
            action: 'role_updated',
            module: 'Employees',
            entityType: 'User',
            entityId: (string) $employee->id,
            oldValue: ['roles' => $previousRoles],
            newValue: ['roles' => [$role]],
        );

        return $employee;
    }

    public function deactivate(User $employee, User $actor): void
    {
        $employee->employeeProfile()->update(['is_active' => false]);
        $employee->tokens()->delete();

        $this->auditLogger->log(
            actor: $actor,
            action: 'deactivated',
            module: 'Employees',
            entityType: 'User',
            entityId: (string) $employee->id,
        );
    }

    private function generateEmployeeId(): string
    {
        do {
            $id = 'EMP-'.strtoupper(Str::random(6));
        } while (EmployeeProfile::query()->where('employee_id', $id)->exists());

        return $id;
    }
}

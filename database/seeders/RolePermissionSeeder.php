<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'product.view', 'product.create', 'product.update', 'product.delete',
        'order.view', 'order.create', 'order.update', 'order.cancel', 'order.refund',
        'vendor.view', 'vendor.approve', 'vendor.suspend',
        'customer.view', 'customer.update', 'customer.block',
        'coupon.view', 'coupon.create', 'coupon.update', 'coupon.delete',
        'payment.view', 'payment.manage',
        'digital_delivery.view', 'digital_delivery.reveal', 'digital_delivery.revoke', 'digital_delivery.reissue',
        'report.view', 'setting.manage',
        'employee.view', 'employee.manage', 'role.manage',
        'entrepreneur.view', 'entrepreneur.register', 'entrepreneur.verify',
        'entrepreneur.suspend', 'entrepreneur.marketplace', 'entrepreneur.order.create',
        'entrepreneur.order.view', 'entrepreneur.wallet.view', 'withdrawal.request',
        'withdrawal.approve', 'withdrawal.reject',
    ];

    /**
     * Roles from spec section 4. Super Admin gets every permission;
     * everyone else gets nothing by default — granular permissions are
     * assigned per-employee by a Super Admin via the admin panel (Phase 7),
     * not hardcoded here, per the spec's "granular permission" requirement.
     *
     * @var array<int, string>
     */
    private array $roles = [
        'super_admin', 'admin', 'manager', 'product_manager', 'order_manager',
        'inventory_manager', 'marketing_manager', 'support_agent', 'accountant',
        'vendor', 'entrepreneur', 'marketing_officer', 'boosting_team',
        'warehouse_manager', 'finance_manager', 'deliveryman', 'customer',
    ];

    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($this->roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName('super_admin')->syncPermissions($this->permissions);
    }
}

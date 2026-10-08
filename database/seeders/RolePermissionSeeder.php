<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissionNames = [
            'admin.access' => 'Access admin panel',
            'catalog.view' => 'View catalog',
            'catalog.manage' => 'Manage catalog',
            'categories.manage' => 'Manage categories',
            'services.manage' => 'Manage services',
            'rental.manage' => 'Manage rentals',
            'requests.manage' => 'Manage customer requests',
            'quotes.manage' => 'Manage quotations',
            'orders.manage' => 'Manage orders',
            'projects.manage' => 'Manage projects',
            'vendors.manage' => 'Manage vendors',
            'suppliers.manage' => 'Manage suppliers',
            'delivery.manage' => 'Manage delivery',
            'finance.view' => 'View finance reports',
            'payments.manage' => 'Manage payments',
            'commissions.manage' => 'Manage commissions',
            'users.manage' => 'Manage users and roles',
            'settings.manage' => 'Manage system settings',
        ];

        $permissionIds = [];

        foreach ($permissionNames as $slug => $name) {
            $permission = Permission::updateOrCreate(['slug' => $slug], ['name' => $name]);
            $permissionIds[$slug] = $permission->id;
        }

        $rolePermissions = [
            'super_admin' => array_keys($permissionNames),
            'sales' => ['admin.access', 'requests.manage', 'quotes.manage', 'orders.manage'],
            'product_manager' => ['admin.access', 'catalog.view', 'catalog.manage', 'categories.manage', 'suppliers.manage'],
            'project_manager' => ['admin.access', 'services.manage', 'requests.manage', 'projects.manage', 'vendors.manage'],
            'rental_manager' => ['admin.access', 'catalog.view', 'rental.manage'],
            'finance' => ['admin.access', 'orders.manage', 'finance.view', 'payments.manage', 'commissions.manage'],
            'customer' => [],
        ];

        foreach ($rolePermissions as $slug => $permissions) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => ucwords(str_replace('_', ' ', $slug))]
            );
            $role->permissions()->sync(array_map(function ($permissionSlug) use ($permissionIds) {
                return $permissionIds[$permissionSlug];
            }, $permissions));
        }

        $superAdmin = User::where('email', 'superadmin@balilivingassist.com')->first();
        $superAdminRole = Role::where('slug', 'super_admin')->first();

        if ($superAdmin && $superAdminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}

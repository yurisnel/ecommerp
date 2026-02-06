<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users'],
            
            // Role Management
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'module' => 'roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'module' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'module' => 'roles'],
            
            // Product Management
            ['name' => 'View Products', 'slug' => 'products.view', 'module' => 'products'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'module' => 'products'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'module' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'module' => 'products'],
            
            // Inventory Management
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory'],
            ['name' => 'Create Inventory Entry', 'slug' => 'inventory.create', 'module' => 'inventory'],
            ['name' => 'Edit Inventory', 'slug' => 'inventory.edit', 'module' => 'inventory'],
            ['name' => 'Adjust Inventory', 'slug' => 'inventory.adjust', 'module' => 'inventory'],
            
            // Sales Management
            ['name' => 'View Sales', 'slug' => 'sales.view', 'module' => 'sales'],
            ['name' => 'Create Sales', 'slug' => 'sales.create', 'module' => 'sales'],
            ['name' => 'Edit Sales', 'slug' => 'sales.edit', 'module' => 'sales'],
            ['name' => 'Cancel Sales', 'slug' => 'sales.cancel', 'module' => 'sales'],
            
            // Supplier Management
            ['name' => 'View Suppliers', 'slug' => 'suppliers.view', 'module' => 'suppliers'],
            ['name' => 'Create Suppliers', 'slug' => 'suppliers.create', 'module' => 'suppliers'],
            ['name' => 'Edit Suppliers', 'slug' => 'suppliers.edit', 'module' => 'suppliers'],
            ['name' => 'Delete Suppliers', 'slug' => 'suppliers.delete', 'module' => 'suppliers'],
            
            // Customer Management
            ['name' => 'View Customers', 'slug' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'module' => 'customers'],
            ['name' => 'Edit Customers', 'slug' => 'customers.edit', 'module' => 'customers'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'module' => 'customers'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create Roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access',
            'status' => 'active',
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manage inventory and sales',
            'status' => 'active',
        ]);

        $salesRole = Role::create([
            'name' => 'Sales Representative',
            'slug' => 'sales',
            'description' => 'Create and manage sales orders',
            'status' => 'active',
        ]);

        $warehouseRole = Role::create([
            'name' => 'Warehouse Staff',
            'slug' => 'warehouse',
            'description' => 'Manage inventory and stock movements',
            'status' => 'active',
        ]);

        // Assign all permissions to Admin
        $adminRole->permissions()->attach(Permission::all());

        // Assign permissions to Manager
        $managerPermissions = Permission::whereIn('module', [
            'products', 'inventory', 'sales', 'suppliers', 'customers', 'reports'
        ])->get();
        $managerRole->permissions()->attach($managerPermissions);

        // Assign permissions to Sales
        $salesPermissions = Permission::whereIn('slug', [
            'products.view',
            'inventory.view',
            'sales.view', 'sales.create', 'sales.edit',
            'customers.view', 'customers.create', 'customers.edit',
        ])->get();
        $salesRole->permissions()->attach($salesPermissions);

        // Assign permissions to Warehouse
        $warehousePermissions = Permission::whereIn('slug', [
            'products.view',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust',
            'suppliers.view',
        ])->get();
        $warehouseRole->permissions()->attach($warehousePermissions);
    }
}

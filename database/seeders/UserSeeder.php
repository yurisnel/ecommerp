<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@ecomerp.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567890',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole);
        }

        // Create Manager User
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@ecomerp.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567891',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $managerRole = Role::where('slug', 'manager')->first();
        if ($managerRole) {
            $manager->roles()->attach($managerRole);
        }

        // Create Sales User
        $sales = User::create([
            'name' => 'Sales Representative',
            'email' => 'sales@ecomerp.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567892',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $salesRole = Role::where('slug', 'sales')->first();
        if ($salesRole) {
            $sales->roles()->attach($salesRole);
        }

        // Create Warehouse User
        $warehouse = User::create([
            'name' => 'Warehouse Staff',
            'email' => 'warehouse@ecomerp.com',
            'password' => Hash::make('password'),
            'phone' => '+1234567893',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $warehouseRole = Role::where('slug', 'warehouse')->first();
        if ($warehouseRole) {
            $warehouse->roles()->attach($warehouseRole);
        }
    }
}

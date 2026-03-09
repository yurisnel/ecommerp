<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            SalesChannelSeeder::class,
            CustomerGroupSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
            SupplierSeeder::class,
            OrderStatusSeeder::class,
            AttributeSeeder::class,
        ]);

        // Dynamic Data
        \App\Models\Warehouse::factory(3)->create();

        \App\Models\Product::factory(50)->create();
        // Customers
        // Customers with Addresses
        \App\Models\Customer::factory(20)->create()->each(function ($customer) {
            \App\Models\CustomerAddress::factory(rand(1, 3))->create([
                'customer_id' => $customer->id
            ]);
        });

        // Orders
        \App\Models\SalesOrder::factory(30)->create();
    }
}

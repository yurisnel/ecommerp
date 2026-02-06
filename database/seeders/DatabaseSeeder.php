<?php

namespace Database\Seeders;

use App\Models\User;
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
            PaymentMethodSeeder::class,
            ShippingMethodSeeder::class,
        ]);

        // Dynamic Data
        \App\Models\Warehouse::factory(3)->create();
        
        // Products and Inventory
        $products = \App\Models\Product::factory(50)->create()->each(function ($product) {
            // Create initial inventory for each product
            \App\Models\ProductEntry::create([
                'entry_number' => 'ENT-' . uniqid(),
                'product_id' => $product->id,
                'warehouse_id' => \App\Models\Warehouse::inRandomOrder()->first()->id,
                'quantity' => $qty = rand(10, 100),
                'cost_per_unit' => $cost = rand(10, 100),
                'selling_price' => $product->price ?? rand(20, 200),
                'total_cost' => $qty * $cost,
                'entry_date' => now(),
            ]);
        });

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

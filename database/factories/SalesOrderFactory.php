<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\SalesChannel;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        $tax = 0;
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'customer_id' => Customer::factory(),
            'sales_channel_id' => SalesChannel::inRandomOrder()->first()?->id ?? 1,
            'warehouse_id' => Warehouse::factory(),
            'order_status_id' => OrderStatus::where('slug', 'pending')->first()?->id ?? 1,
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),            
            'subtotal' => $subtotal = $this->faker->randomFloat(2, 50, 200),
            'tax' => $taxValue = $subtotal * $tax,
            'discount' => $discount = $this->faker->randomFloat(2, 0, 5),
            'shipping' => $shipping = $this->faker->randomFloat(2, 10, 3),
            'total' => $subtotal + $taxValue + $shipping - $discount,
            'shipping_address' => $this->faker->address,
            'billing_address' => $this->faker->address,
        ];
    }

    /**
     * Configure the model factory
     */
    public function configure()
    {
        return $this->afterCreating(function ($salesOrder) {
            // Create initial status history record with 'pending' status
            $pendingStatus = OrderStatus::where('slug', 'pending')->first();
            
            if ($pendingStatus) {
                OrderStatusHistory::create([
                    'sales_order_id' => $salesOrder->id,
                    'order_status_id' => $pendingStatus->id,
                    'changed_at' => $salesOrder->created_at,
                ]);
            }

            // Create sales order items (1-5 items per order)
            $itemCount = fake()->numberBetween(1, 3);
            $products = Product::inRandomOrder()->limit($itemCount)->get();
            $tax = 0;
            $subtotalProducts = 0;
            foreach ($products as $product) {
                $quantity = fake()->numberBetween(1, 5);
                $unitPrice = fake()->randomFloat(2, 10, 20);
                $unitCost = $unitPrice * fake()->randomFloat(1, 0.5, 0.8);
                $discount = fake()->randomFloat(2, 0, $unitPrice * 0.2);
                $subtotal = ($unitPrice * $quantity) - $discount;
                $taxValue = $subtotal * $tax;
                $total = round($subtotal + $taxValue, 2);
                $subtotalProducts += $total;

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'discount' => $discount,
                    'tax' => $taxValue,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'notes' => fake()->optional(0.3)->sentence(),
                ]);
            }
    
             $salesOrder->subtotal = $subtotalProducts;
             $salesOrder->tax = $salesOrder->subtotal * $tax;
             $salesOrder->total = $salesOrder->subtotal + $salesOrder->tax + $salesOrder->shipping - $salesOrder->discount;
             $salesOrder->save();
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesChannel;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'customer_id' => Customer::factory(),
            'sales_channel_id' => SalesChannel::inRandomOrder()->first()?->id ?? 1,
            'warehouse_id' => Warehouse::factory(),
            'order_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'subtotal' => $subtotal = $this->faker->randomFloat(2, 50, 1800),
            'tax' => $tax = $subtotal * 0.1,
            'discount' => $discount = $this->faker->randomFloat(2, 0, 20),
            'shipping' => $shipping = $this->faker->randomFloat(2, 10, 50),
            'total' => $subtotal + $tax + $shipping - $discount,
            'shipping_address' => $this->faker->address,
            'billing_address' => $this->faker->address,
        ];
    }
}

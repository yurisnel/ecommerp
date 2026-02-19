<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'description' => 'Order has been received but not yet confirmed',
                'color' => '#FFA500', // Orange
            ],
            [
                'name' => 'Confirmed',
                'slug' => 'confirmed',
                'description' => 'Order has been confirmed and is awaiting processing',
                'color' => '#4169E1', // Royal Blue
            ],
            [
                'name' => 'Processing',
                'slug' => 'processing',
                'description' => 'Order is being processed and prepared for shipment',
                'color' => '#FFD700', // Gold
            ],
            [
                'name' => 'Shipped',
                'slug' => 'shipped',
                'description' => 'Order has been shipped and is in transit',
                'color' => '#00CED1', // Dark Turquoise
            ],
            [
                'name' => 'Delivered',
                'slug' => 'delivered',
                'description' => 'Order has been successfully delivered',
                'color' => '#32CD32', // Lime Green
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'description' => 'Order has been cancelled',
                'color' => '#DC143C', // Crimson Red
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}

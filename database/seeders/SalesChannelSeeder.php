<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesChannel;

class SalesChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'E-commerce Website',
                'code' => 'ECOM',
                'type' => 'ecommerce',
                'description' => 'Online store sales channel',
                'status' => 'active',
            ],
            [
                'name' => 'Mobile App',
                'code' => 'APP',
                'type' => 'mobile_app',
                'description' => 'Mobile application sales channel',
                'status' => 'active',
            ],
            [
                'name' => 'Physical Store - Main',
                'code' => 'POS-MAIN',
                'type' => 'pos',
                'description' => 'Main physical store point of sale',
                'status' => 'active',
            ],
            [
                'name' => 'Physical Store - Branch 1',
                'code' => 'POS-BR1',
                'type' => 'pos',
                'description' => 'Branch 1 point of sale',
                'status' => 'active',
            ],
            [
                'name' => 'Wholesale',
                'code' => 'WHOLESALE',
                'type' => 'wholesale',
                'description' => 'Wholesale sales channel',
                'status' => 'active',
            ],
        ];

        foreach ($channels as $channel) {
            SalesChannel::create($channel);
        }
    }
}

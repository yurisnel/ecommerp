<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Retail',
                'code' => 'RETAIL',
                'description' => 'Regular retail customers',
                'discount_percentage' => 0,
                'priority' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Wholesale',
                'code' => 'WHOLESALE',
                'description' => 'Wholesale customers with bulk discounts',
                'discount_percentage' => 10,
                'priority' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'VIP',
                'code' => 'VIP',
                'description' => 'VIP customers with premium benefits',
                'discount_percentage' => 15,
                'priority' => 3,
                'status' => 'active',
            ],
            [
                'name' => 'Corporate',
                'code' => 'CORPORATE',
                'description' => 'Corporate accounts',
                'discount_percentage' => 12,
                'priority' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($groups as $group) {
            CustomerGroup::create($group);
        }
    }
}

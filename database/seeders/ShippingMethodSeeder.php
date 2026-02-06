<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingRate;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Shipping Methods
        $standard = ShippingMethod::create([
            'name' => 'Standard Shipping',
            'code' => 'STANDARD',
            'type' => 'standard',
            'description' => 'Standard delivery',
            'estimated_days_min' => 5,
            'estimated_days_max' => 7,
            'requires_config' => false,
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $express = ShippingMethod::create([
            'name' => 'Express Shipping',
            'code' => 'EXPRESS',
            'type' => 'express',
            'description' => 'Fast delivery',
            'estimated_days_min' => 2,
            'estimated_days_max' => 3,
            'requires_config' => false,
            'sort_order' => 2,
            'status' => 'active',
        ]);

        $pickup = ShippingMethod::create([
            'name' => 'Store Pickup',
            'code' => 'PICKUP',
            'type' => 'pickup',
            'description' => 'Pick up at store',
            'estimated_days_min' => 1,
            'estimated_days_max' => 1,
            'requires_config' => false,
            'sort_order' => 3,
            'status' => 'active',
        ]);

        // Create Shipping Zones
        $domestic = ShippingZone::create([
            'name' => 'Domestic',
            'code' => 'DOMESTIC',
            'description' => 'Domestic shipping zone',
            'countries' => ['US', 'MX'],
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $international = ShippingZone::create([
            'name' => 'International',
            'code' => 'INTERNATIONAL',
            'description' => 'International shipping zone',
            'countries' => ['*'], // All countries
            'sort_order' => 2,
            'status' => 'active',
        ]);

        // Create Shipping Rates
        // Standard - Domestic
        ShippingRate::create([
            'shipping_method_id' => $standard->id,
            'shipping_zone_id' => $domestic->id,
            'name' => 'Standard Domestic',
            'calculation_type' => 'fixed',
            'base_rate' => 5.00,
            'min_order_amount' => 50.00, // Free shipping over $50
        ]);

        // Express - Domestic
        ShippingRate::create([
            'shipping_method_id' => $express->id,
            'shipping_zone_id' => $domestic->id,
            'name' => 'Express Domestic',
            'calculation_type' => 'fixed',
            'base_rate' => 15.00,
        ]);

        // Pickup - Domestic
        ShippingRate::create([
            'shipping_method_id' => $pickup->id,
            'shipping_zone_id' => $domestic->id,
            'name' => 'Store Pickup',
            'calculation_type' => 'fixed',
            'base_rate' => 0.00,
            'is_free_shipping' => true,
        ]);

        // Standard - International
        ShippingRate::create([
            'shipping_method_id' => $standard->id,
            'shipping_zone_id' => $international->id,
            'name' => 'Standard International',
            'calculation_type' => 'weight_based',
            'base_rate' => 20.00,
            'rate_per_unit' => 2.00, // $2 per kg
        ]);

        // Express - International
        ShippingRate::create([
            'shipping_method_id' => $express->id,
            'shipping_zone_id' => $international->id,
            'name' => 'Express International',
            'calculation_type' => 'weight_based',
            'base_rate' => 40.00,
            'rate_per_unit' => 4.00, // $4 per kg
        ]);
    }
}

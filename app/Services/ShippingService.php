<?php

namespace App\Services;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingRate;
use App\Models\CustomerAddress;
use Illuminate\Support\Collection;

/**
 * Shipping Service
 * Calculates shipping costs and finds available shipping methods
 */
class ShippingService
{
    /**
     * Get available shipping methods for an address
     * 
     * @param CustomerAddress $address
     * @param float $orderAmount
     * @param float $weight
     * @param int $itemCount
     * @return Collection
     */
    public function getAvailableShippingMethods(CustomerAddress $address, float $orderAmount = 0, float $weight = 0, int $itemCount = 0): Collection
    {
        // Find matching shipping zone
        $zone = $this->findMatchingZone($address);

        if (!$zone) {
            return collect();
        }

        // Get active shipping methods with rates for this zone
        $methods = ShippingMethod::where('status', 'active')
            ->whereHas('shippingRates', function ($query) use ($zone) {
                $query->where('shipping_zone_id', $zone->id);
            })
            ->with(['shippingRates' => function ($query) use ($zone) {
                $query->where('shipping_zone_id', $zone->id);
            }])
            ->orderBy('sort_order')
            ->get();

        // Calculate cost for each method
        $methodsWithCosts = $methods->map(function ($method) use ($orderAmount, $weight, $itemCount) {
            $rate = $method->shippingRates->first();
            $cost = $rate ? $rate->calculateCost($orderAmount, $weight, $itemCount) : 0;

            return [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'type' => $method->type,
                'description' => $method->description,
                'estimated_delivery' => $method->estimated_delivery,
                'cost' => $cost,
                'is_free' => $cost == 0,
            ];
        });

        // Filter out methods with 0 cost (not applicable due to weight/other limits)
        // unless they are actually free shipping
        return $methodsWithCosts->filter(function ($method) use ($orderAmount, $weight, $itemCount) {
            $rate = ShippingRate::where('shipping_method_id', $method['id'])->first();
            return $method['cost'] > 0 || ($rate && $rate->is_free_shipping);
        });
    }

    /**
     * Find matching shipping zone for an address
     * 
     * @param CustomerAddress $address
     * @return ShippingZone|null
     */
    public function findMatchingZone(CustomerAddress $address): ?ShippingZone
    {
        $zones = ShippingZone::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        foreach ($zones as $zone) {
            if ($zone->matchesAddress(
                $address->country,
                $address->state,
                $address->city,
                $address->postal_code
            )) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Calculate shipping cost for a specific method
     * 
     * @param int $shippingMethodId
     * @param CustomerAddress $address
     * @param float $orderAmount
     * @param float $weight
     * @param int $itemCount
     * @return float
     */
    public function calculateShippingCost(int $shippingMethodId, CustomerAddress $address, float $orderAmount = 0, float $weight = 0, int $itemCount = 0): float
    {
        $zone = $this->findMatchingZone($address);

        if (!$zone) {
            return 0;
        }

        $rate = ShippingRate::where('shipping_method_id', $shippingMethodId)
            ->where('shipping_zone_id', $zone->id)
            ->first();

        if (!$rate) {
            return 0;
        }

        return $rate->calculateCost($orderAmount, $weight, $itemCount);
    }

    /**
     * Get cheapest shipping option
     * 
     * @param CustomerAddress $address
     * @param float $orderAmount
     * @param float $weight
     * @param int $itemCount
     * @return array|null
     */
    public function getCheapestShipping(CustomerAddress $address, float $orderAmount = 0, float $weight = 0, int $itemCount = 0): ?array
    {
        $methods = $this->getAvailableShippingMethods($address, $orderAmount, $weight, $itemCount);

        if ($methods->isEmpty()) {
            return null;
        }

        return $methods->sortBy('cost')->first();
    }

    /**
     * Get fastest shipping option
     * 
     * @param CustomerAddress $address
     * @param float $orderAmount
     * @param float $weight
     * @param int $itemCount
     * @return array|null
     */
    public function getFastestShipping(CustomerAddress $address, float $orderAmount = 0, float $weight = 0, int $itemCount = 0): ?array
    {
        $methods = $this->getAvailableShippingMethods($address, $orderAmount, $weight, $itemCount);

        if ($methods->isEmpty()) {
            return null;
        }

        // Sort by estimated_days_min (methods with pickup or express are usually faster)
        return $methods->sortBy(function ($method) {
            $shippingMethod = ShippingMethod::find($method['id']);
            return $shippingMethod->estimated_days_min ?? 999;
        })->first();
    }
}

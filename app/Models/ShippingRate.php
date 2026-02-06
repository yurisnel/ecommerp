<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_method_id',
        'shipping_zone_id',
        'name',
        'calculation_type',
        'base_rate',
        'rate_per_unit',
        'min_order_amount',
        'max_weight',
        'is_free_shipping',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'is_free_shipping' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the shipping method for this rate
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Get the shipping zone for this rate
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(float $orderAmount, float $weight = 0, int $itemCount = 0): float
    {
        // Check for free shipping
        if ($this->is_free_shipping) {
            return 0;
        }

        if ($this->min_order_amount && $orderAmount >= $this->min_order_amount) {
            return 0;
        }

        // Check weight limit
        if ($this->max_weight && $weight > $this->max_weight) {
            return 0; // Not applicable
        }

        $cost = $this->base_rate;

        switch ($this->calculation_type) {
            case 'weight_based':
                if ($this->rate_per_unit) {
                    $cost += $weight * $this->rate_per_unit;
                }
                break;

            case 'price_based':
                if ($this->rate_per_unit) {
                    $cost += $orderAmount * ($this->rate_per_unit / 100);
                }
                break;

            case 'item_based':
                if ($this->rate_per_unit) {
                    $cost += $itemCount * $this->rate_per_unit;
                }
                break;

            case 'fixed':
            default:
                // Cost is just base_rate
                break;
        }

        return $cost;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'icon',
        'estimated_days_min',
        'estimated_days_max',
        'requires_config',
        'config_fields',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'estimated_days_min' => 'integer',
        'estimated_days_max' => 'integer',
        'requires_config' => 'boolean',
        'config_fields' => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the shipping rates for this method
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Get the sales orders using this method
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Check if method is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get estimated delivery time as string
     */
    public function getEstimatedDeliveryAttribute(): string
    {
        if ($this->estimated_days_min && $this->estimated_days_max) {
            return "{$this->estimated_days_min}-{$this->estimated_days_max} days";
        } elseif ($this->estimated_days_min) {
            return "{$this->estimated_days_min}+ days";
        }
        
        return 'Contact for estimate';
    }
}

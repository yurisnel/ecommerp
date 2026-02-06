<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'countries',
        'states',
        'cities',
        'postal_codes',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'countries' => 'array',
        'states' => 'array',
        'cities' => 'array',
        'postal_codes' => 'array',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the shipping rates for this zone
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Check if address matches this zone
     */
    public function matchesAddress(string $country, ?string $state = null, ?string $city = null, ?string $postalCode = null): bool
    {
        // Check country
        if ($this->countries && !in_array($country, $this->countries)) {
            return false;
        }

        // Check state
        if ($this->states && $state && !in_array($state, $this->states)) {
            return false;
        }

        // Check city
        if ($this->cities && $city && !in_array($city, $this->cities)) {
            return false;
        }

        // Check postal code (simple pattern matching)
        if ($this->postal_codes && $postalCode) {
            $matched = false;
            foreach ($this->postal_codes as $pattern) {
                if (fnmatch($pattern, $postalCode)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }
        }

        return true;
    }
}

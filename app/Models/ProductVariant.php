<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'barcode',
        'weight',
        'image_url',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'total_stock',
        'total_quantity',
        'full_name',
    ];

    /**
     * Get the product that owns this variant
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attribute values for this variant
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    /**
     * Get inventory records for this variant (using existing table)
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_variant_id');
    }

    /**
     * Get stock movements for this variant (using existing table)
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }

    /**
     * Get total available stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventory()->sum('available_quantity');
    }

    /**
     * Get total quantity across all warehouses
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->inventory()->sum('quantity');
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->total_stock > 0;
    }

    /**
     * Get display name with attribute values
     */
    public function getFullNameAttribute(): string
    {
        $values = $this->attributeValues->pluck('value')->toArray();
        return $this->name . ($values ? ' - ' . implode(', ', $values) : '');
    }
}

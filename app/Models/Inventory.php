<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product for this inventory record
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this inventory record
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the variant for this inventory record (if any)
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Update available quantity
     */
    public function updateAvailableQuantity(): void
    {
        $this->available_quantity = $this->quantity - $this->reserved_quantity;
        $this->save();
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Check if stock is below minimum
     */
    public function isBelowMinimum(): bool
    {
        return $this->quantity < $this->product->min_stock;
    }
}

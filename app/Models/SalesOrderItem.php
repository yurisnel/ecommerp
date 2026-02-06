<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'product_entry_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'discount',
        'tax',
        'subtotal',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sales order for this item
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the product for this item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product entry for this item
     */
    public function productEntry(): BelongsTo
    {
        return $this->belongsTo(ProductEntry::class);
    }

    /**
     * Calculate profit for this item
     */
    public function getProfitAttribute(): float
    {
        if (!$this->unit_cost) {
            return 0;
        }
        
        return ($this->unit_price - $this->unit_cost) * $this->quantity;
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitMarginAttribute(): float
    {
        if (!$this->unit_cost || $this->unit_cost == 0) {
            return 0;
        }
        
        return (($this->unit_price - $this->unit_cost) / $this->unit_cost) * 100;
    }
}

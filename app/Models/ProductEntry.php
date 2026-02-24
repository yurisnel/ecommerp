<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'warehouse_id',
        'quantity',
        'unit_cost',
        'unit_price',        
        'entry_date',
        'expiration_date',
        'batch_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'entry_date' => 'date',
        'expiration_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the product for this entry
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier for this entry
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse for this entry
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who created this entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the stock movements for this entry
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the sales order items from this entry
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->unit_cost == 0) {
            return 0;
        }
        
        return (($this->unit_price - $this->unit_cost) / $this->unit_cost) * 100;
    }
}

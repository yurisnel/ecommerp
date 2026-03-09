<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'barcode',
        'unit',
        'min_stock',
        'max_stock',
        'status',
    ];

    protected $casts = [
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];



    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the default image for the product.
     */
    public function defaultImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }

    /**
     * Get the product entries for this product
     */
    public function productEntries(): HasMany
    {
        return $this->hasMany(ProductEntry::class);
    }

    /**
     * Get the latest product entry (for cost/price info)
     */
    public function latestEntry()
    {
        return $this->hasOne(ProductEntry::class)->latestOfMany();
    }

    /**
     * Get the stock movements for this product
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the inventory records for this product
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the sales order items for this product
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get total available stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventory()->sum('available_quantity');
    }

    /**
     * Get the variants for this product
     */
    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the attributes for this product
     */
    public function productAttributes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Get the attributes assigned to this product
     */
    public function attributes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot(['is_variant_attribute', 'is_filterable', 'is_required', 'sort_order'])
            ->withTimestamps();
    }
}

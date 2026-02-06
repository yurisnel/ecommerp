<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class DiscountRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'customer_group_id',
        'applies_to',
        'min_quantity',
        'min_amount',
        'max_discount',
        'start_date',
        'end_date',
        'priority',
        'combinable',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_quantity' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'priority' => 'integer',
        'combinable' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the customer group for this discount rule
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Get the products this discount applies to
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_rule_products');
    }

    /**
     * Get the categories this discount applies to
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_rule_categories');
    }

    /**
     * Get the sales orders that used this discount
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Check if discount is currently active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if discount applies to a specific product
     */
    public function appliesToProduct(int $productId, ?int $categoryId = null): bool
    {
        if ($this->applies_to === 'all') {
            return true;
        }

        if ($this->applies_to === 'products') {
            return $this->products()->where('product_id', $productId)->exists();
        }

        if ($this->applies_to === 'categories' && $categoryId) {
            return $this->categories()->where('category_id', $categoryId)->exists();
        }

        return false;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $amount, float $quantity = 1): float
    {
        // Check minimum requirements
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return 0;
        }

        if ($this->min_amount && $amount < $this->min_amount) {
            return 0;
        }

        // Calculate discount
        $discount = 0;

        if ($this->type === 'percentage') {
            $discount = $amount * ($this->value / 100);
        } else {
            $discount = $this->value;
        }

        // Apply maximum discount limit
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return $discount;
    }
}

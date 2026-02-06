<?php

namespace App\Services;

use App\Models\DiscountRule;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Discount Service
 * Calculates applicable discounts for customers, products, and orders
 */
class DiscountService
{
    /**
     * Get applicable discount rules for a customer and product
     * 
     * @param int|null $customerId
     * @param int $productId
     * @param int|null $categoryId
     * @param float $quantity
     * @param float $amount
     * @return Collection
     */
    public function getApplicableDiscounts(?int $customerId, int $productId, ?int $categoryId = null, float $quantity = 1, float $amount = 0): Collection
    {
        $query = DiscountRule::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });

        // Filter by customer group if customer provided
        if ($customerId) {
            $customer = Customer::with('customerGroup')->find($customerId);
            if ($customer && $customer->customer_group_id) {
                $query->where(function ($q) use ($customer) {
                    $q->whereNull('customer_group_id')
                        ->orWhere('customer_group_id', $customer->customer_group_id);
                });
            }
        }

        $rules = $query->orderBy('priority', 'desc')->get();

        // Filter rules that apply to this product
        $applicableRules = $rules->filter(function ($rule) use ($productId, $categoryId, $quantity, $amount) {
            // Check if rule applies to product/category
            if (!$rule->appliesToProduct($productId, $categoryId)) {
                return false;
            }

            // Check minimum requirements
            if ($rule->min_quantity && $quantity < $rule->min_quantity) {
                return false;
            }

            if ($rule->min_amount && $amount < $rule->min_amount) {
                return false;
            }

            return true;
        });

        return $applicableRules;
    }

    /**
     * Calculate best discount for a product
     * 
     * @param int|null $customerId
     * @param int $productId
     * @param int|null $categoryId
     * @param float $quantity
     * @param float $amount
     * @return array ['discount' => float, 'rule' => DiscountRule|null]
     */
    public function calculateBestDiscount(?int $customerId, int $productId, ?int $categoryId = null, float $quantity = 1, float $amount = 0): array
    {
        $applicableRules = $this->getApplicableDiscounts($customerId, $productId, $categoryId, $quantity, $amount);

        if ($applicableRules->isEmpty()) {
            return ['discount' => 0, 'rule' => null];
        }

        $bestDiscount = 0;
        $bestRule = null;

        foreach ($applicableRules as $rule) {
            $discount = $rule->calculateDiscount($amount, $quantity);
            
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestRule = $rule;
            }
        }

        return [
            'discount' => $bestDiscount,
            'rule' => $bestRule,
        ];
    }

    /**
     * Calculate discounts for multiple items (shopping cart)
     * 
     * @param int|null $customerId
     * @param array $items [['product_id' => int, 'category_id' => int, 'quantity' => float, 'price' => float], ...]
     * @return array
     */
    public function calculateCartDiscounts(?int $customerId, array $items): array
    {
        $totalDiscount = 0;
        $itemDiscounts = [];
        $appliedRules = [];

        foreach ($items as $item) {
            $amount = $item['price'] * $item['quantity'];
            
            $result = $this->calculateBestDiscount(
                $customerId,
                $item['product_id'],
                $item['category_id'] ?? null,
                $item['quantity'],
                $amount
            );

            if ($result['discount'] > 0) {
                $totalDiscount += $result['discount'];
                $itemDiscounts[$item['product_id']] = $result['discount'];
                
                if ($result['rule'] && !in_array($result['rule']->id, $appliedRules)) {
                    $appliedRules[] = $result['rule']->id;
                }
            }
        }

        return [
            'total_discount' => $totalDiscount,
            'item_discounts' => $itemDiscounts,
            'applied_rules' => $appliedRules,
        ];
    }

    /**
     * Get customer group default discount
     * 
     * @param int $customerId
     * @return float
     */
    public function getCustomerGroupDiscount(int $customerId): float
    {
        $customer = Customer::with('customerGroup')->find($customerId);
        
        if ($customer && $customer->customerGroup) {
            return $customer->customerGroup->discount_percentage;
        }

        return 0;
    }
}

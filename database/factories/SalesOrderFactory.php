<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\SalesChannel;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\SalesService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SalesOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SalesOrder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            // Empty - we use a different approach
        ];
    }

    /**
     * Create model(s) using SalesService
     *
     * @param array $attributes
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     * @return SalesOrder|\Illuminate\Database\Eloquent\Collection|int
     */
    public function create($attributes = [], $parent = null)
    {
        // If count is set, create multiple
        if ($this->count !== null && $this->count > 1) {
            return $this->createMultiple($this->count, $attributes);
        }

        return $this->createSingleOrder($attributes);
    }

    /**
     * Create multiple orders
     *
     * @param int $count
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createMultiple(int $count, array $attributes = []): \Illuminate\Support\Collection
    {
        $results = collect();

        for ($i = 0; $i < $count; $i++) {
            $order = $this->createSingleOrder($attributes);
            if ($order) {
                $results->push($order);
            }
        }

        return $results;
    }

    /**
     * Internal method to create a single order using SalesService
     *
     * @param array $attributes
     * @return SalesOrder|null
     */
    protected function createSingleOrder(array $attributes = []): ?SalesOrder
    {
        $salesService = App::make(SalesService::class);

        // Get or create customer
        $customer = $attributes['customer'] ?? Customer::factory()->create();

        // Get or create warehouse
        //$warehouse = $attributes['warehouse'] ?? Warehouse::inRandomOrder()->first() ?? Warehouse::factory()->create();

        // Prepare items data
        $itemCount = fake()->numberBetween(1, 3);
        $productsEntry = ProductEntry::inRandomOrder()->limit($itemCount)->get();
        $items = [];
        $tax = 0;

        foreach ($productsEntry as $productEntry) {
            $quantity = fake()->numberBetween(1, 5);
            $unitPrice = $productEntry->unit_price;         
            $discount = random_int(0, $unitPrice * 0.2); // Random discount between 0% and 20%

            $items[] = [
                'product_entry_id' => $productEntry->id,
                'quantity' => $quantity,
                'discount' => $discount
            ];
        }

        // Prepare data for SalesService
        $orderData = [
            'customer_id' => $customer->id ?? null,
            'sales_channel_id' => $attributes['sales_channel_id'] ?? SalesChannel::inRandomOrder()->first()?->id ?? 1,
            'warehouse_id' => $warehouse?->id ?? 1,
            'order_date' => $attributes['order_date'] ?? fake()->dateTimeBetween('-1 year', 'now'),
            'shipping_address' => $attributes['shipping_address'] ?? fake()->address,
            'billing_address' => $attributes['billing_address'] ?? fake()->address,
            'tax' => $attributes['tax'] ?? $tax,
            'discount_global' => $attributes['discount_global'] ?? fake()->randomFloat(2, 0, 2),
            'shipping' => $attributes['shipping'] ?? fake()->randomFloat(2, 1, 5),
            'items' => $items,
        ];

        $order = null;
        try {
            $order = $salesService->createSalesOrder($orderData);
            $this->createPayments($order);
        } catch (\Exception $e) {
            Log::error('Error in SalesOrder Factory: ' . $e->getMessage());
        }

        return $order;
    }

    /**
     * Create payments for the order (optional random)
     *
     * @param SalesOrder $salesOrder
     * @return void
     */
    public function createPayments(SalesOrder $salesOrder): void
    {
        $shouldCreatePayment = fake()->boolean(70);

        if (!$shouldCreatePayment) {
            return;
        }

        try {
            $salesService = App::make(SalesService::class);
            $paymentMethod = PaymentMethod::inRandomOrder()->first();

            if (!$paymentMethod) {
                Log::warning('No payment methods available for order #' . $salesOrder->order_number);
                return;
            }

            // Decide randomly if partially or fully paid
            $isFullyPaid = fake()->boolean(60);

            if ($isFullyPaid) {
                // Full payment
                $amount = $salesOrder->total;
            } else {
                // Partial payment (50-90% of total)
                $percentage = fake()->randomFloat(2, 0.5, 0.9);
                $amount = round($salesOrder->total * $percentage, 2);
            }

            $salesService->processPayment([
                'sales_order_id' => $salesOrder->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amount,
                'status' => 'completed',
                'payment_date' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating payment for order #' . $salesOrder->order_number . ': ' . $e->getMessage());
        }
    }
}

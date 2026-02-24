<?php

namespace App\Services;

use App\Enums\EOrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Payment;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\ProductEntry;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Sales Service
 * Manages sales orders, payments, and inventory deduction
 */
class SalesService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new sales order
     * 
     * @param array $data
     * @return SalesOrder
     * @throws Exception
     */
    public function createSalesOrder(array $data): SalesOrder
    {
        DB::beginTransaction();

        try {
            // Generate order number if not provided
            if (!isset($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }

            // Set order date if not provided
            if (!isset($data['order_date'])) {
                $data['order_date'] = now();
            }

            // Create sales order
            $orderStatusId = OrderStatus::where('slug', EOrderStatus::PENDING)->first()->id ?? null;
            $discountGlobal = $data['discount_global'] ?? 0;
            $taxRate = $data['tax_rate'] ?? 0;
            
            $order = SalesOrder::create([
                'order_number' => $data['order_number'],
                'customer_id' => $data['customer_id'] ?? null,
                'sales_channel_id' => $data['sales_channel_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_status_id' => $orderStatusId,
                'tax_rate' => $taxRate,
                'tax_amount' => 0,
                'discount_global' => $discountGlobal,
                'discount_total' => 0,
                'shipping' => $data['shipping'] ?? 0,
                'subtotal' => 0,
                'subtotal_after_discount' => 0,
                'total' => 0,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'order_date' => $data['order_date'],
                'created_by' => $data['created_by'] ?? null,
            ]);

            // Add order items
            $lineSubtotal = 0;
            $lineDiscountTotal = 0;
            $lineTaxAmountTotal = 0;
            $subtotalAfterLineDiscount = 0;
            foreach ($data['items'] as $itemData) {
                $itemData['tax_rate'] = $taxRate; // Impuesto en Por Ciento
                $item = $this->addOrderItem($order, $itemData);
                $lineSubtotal += $item->subtotal;
                $subtotalAfterLineDiscount += $item->subtotal_after_discount;
                $lineDiscountTotal += $item->discount;
                $lineTaxAmountTotal += $item->tax_amount;
            }
            //$globalDiscount = $subtotalAfterLineDiscount * ($data['discount'] / 100);
          
            $orderDiscountTotal = $lineDiscountTotal + $discountGlobal;

            // Update order totals
            $order->subtotal = $lineSubtotal;
            $order->discount_global = $discountGlobal;
            $order->discount_total = $orderDiscountTotal;
            $order->tax_rate = $taxRate;
            $order->tax_amount = $lineTaxAmountTotal;
            $order->subtotal_after_discount = $subtotalAfterLineDiscount;
            $order->total = $lineSubtotal - $orderDiscountTotal + $lineTaxAmountTotal + $order->shipping;

            $order->save();

            // Create initial status history
            $history = new OrderStatusHistory();
            $history->sales_order_id = $order->id;
            $history->order_status_id = $orderStatusId;
            $history->changed_by = auth()->id();
            $history->changed_at = now();
            $history->save();

            DB::commit();

            return $order->fresh(['items.product', 'customer', 'salesChannel', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * Add item to sales order
     * 
     * @param SalesOrder $order
     * @param array $data
     * @return SalesOrderItem
     * @throws Exception
     */
    public function addOrderItem(SalesOrder $order, array $data): SalesOrderItem
    {
        $productEntryId = $data['product_entry_id'];        
        $productEntry = ProductEntry::findOrFail($productEntryId);

        // Reserve inventory
        $this->inventoryService->reserveInventory(
            $productEntry->product_id,
            $order->warehouse_id,
            $data['quantity']
        );

        // Calculate item totals
        $unitPrice = $productEntry->unit_price;
        $subtotal = round($data['quantity'] * $unitPrice, 2);
        //$discount = $subtotal * ($data['discount'] / 100);
        $discount  = $data['discount'] ?? 0;
        $subtotalAfterDiscount = round($subtotal - $discount, 2);
        $taxRate  = $data['tax_rate'] ?? 1;
        $taxAmount = round($subtotalAfterDiscount * $taxRate, 2);


        return SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $productEntry->product_id,
            'product_entry_id' => $productEntry->id,
            'unit_price' => $unitPrice,
            'unit_cost' => $productEntry->unit_cost,
            'quantity' => $data['quantity'],
            'discount' => $discount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'total' => $subtotalAfterDiscount + $taxAmount,
            'notes' => $data['notes'] ?? null,
        ]);
    }


    /**
     * Transition order to a new status with validation
     */
    public function updateStatus(int $orderId, $data): SalesOrder
    {
        $toStatus = $data['order_status'] ?? null;
        DB::beginTransaction();

        try {
            $order = SalesOrder::with('orderStatus')->findOrFail($orderId);

            $fromStatus = $order->orderStatus->slug ?? null;

            if (!EOrderStatus::isValidTransition($fromStatus, $toStatus)) {
                throw new Exception("Cannot transition from {$fromStatus} to {$toStatus}");
            }

            switch ($toStatus) {
                case EOrderStatus::CONFIRMED:
                    $this->confirmOrder($order);
                    break;
                case EOrderStatus::CANCELLED:
                    $this->cancelOrder($order);
                    break;
            }


            $newStatus = OrderStatus::where('slug', $toStatus)->firstOrFail();
            $order->order_status_id = $newStatus->id;
            $order->save();

            $history = new OrderStatusHistory();
            $history->sales_order_id = $order->id;
            $history->order_status_id = $newStatus->id;
            $history->changed_by = auth()->id();
            $history->changed_at = $data['changed_at'] ?? now();
            $history->notes = $data['notes'] ?? null;

            $history->save();
            DB::commit();
            event(new OrderStatusChanged($order, $fromStatus, $toStatus));

            return $order->fresh(['orderStatus', 'statusHistories.status', 'statusHistories.changer']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Coonfirm Reserve
     * 
     * @param SalesOrder $order
     * @return bool
     * 
     */
    protected function confirmOrder($order): bool
    {
        // Process each item
        foreach ($order->items as $item) {
            // Release reservation
            $this->inventoryService->releaseReservedInventory(
                $item->product_id,
                $order->warehouse_id,
                $item->quantity
            );

            // Deduct from inventory
            $this->inventoryService->updateInventory(
                $item->product_id,
                $order->warehouse_id,
                $item->quantity,
                'subtract'
            );

            // Create stock movement
            $this->inventoryService->createStockMovement([
                'product_id' => $item->product_id,
                'warehouse_id' => $order->warehouse_id,
                'product_entry_id' => $item->product_entry_id,
                'type' => 'out',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'reference_type' => 'sales_order',
                'reference_id' => $order->id,
                'notes' => 'Sale order: ' . $order->order_number,
                'created_by' => auth()->id(),
                'movement_date' => now(),
            ]);
        }
        return true;
    }

    /**
     * Cancel sales order and release inventory
     * 
     * @param SalesOrder $orderId
     * @return bool     *
     */
    public function cancelOrder($order): bool
    {
        $currentStatus = $order->currentStatus?->slug;

        foreach ($order->items as $item) {
            // Release reserved inventory for pending orders

            foreach ($order->items as $item) {
                if ($currentStatus === EOrderStatus::PENDING) {
                    $this->inventoryService->releaseReservedInventory(
                        $item->product_id,
                        $order->warehouse_id,
                        $item->quantity
                    );
                } else  if (in_array($currentStatus, [EOrderStatus::CONFIRMED, EOrderStatus::PROCESSING, EOrderStatus::SHIPPED])) {

                    // Return inventory for confirmed/processing orders
                    $this->inventoryService->updateInventory(
                        $item->product_id,
                        $order->warehouse_id,
                        $item->quantity,
                        'add'
                    );

                    // Create stock movement for return
                    $this->inventoryService->createStockMovement([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'sales_order_cancellation',
                        'reference_id' => $order->id,
                        'notes' => 'Order cancellation: ' . $order->order_number,
                        'created_by' => auth()->id(),
                        'movement_date' => now(),
                    ]);
                }
            }
        }
        return true;
    }

    /**
     * Process payment for sales order
     * 
     * @param array $data
     * @return Payment
     * @throws Exception
     */
    public function processPayment(array $data): Payment
    {
        DB::beginTransaction();

        try {
            $order = SalesOrder::findOrFail($data['sales_order_id']);

            $payment = Payment::create([
                'sales_order_id' => $data['sales_order_id'],
                'payment_method_id' => $data['payment_method_id'],
                'amount' => $data['amount'],
                'status' => $data['status'] ?? 'completed',
                'transaction_id' => $data['transaction_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'payment_date' => $data['payment_date'] ?? now(),
                'processed_by' => $data['processed_by'] ?? auth()->id(),
            ]);

            // Check if order is fully paid and update status to confirmed
            // Calculate total paid amount for this order
            $totalPaid = $order->payments()
                ->where('status', 'completed')
                ->sum('amount');

            // Check if fully paid (with small tolerance for floating point)
            if ($totalPaid >= $order->total - 0.01) {
                // Update status to confirmed
                $this->updateStatus($order->id, [
                    'order_status' => EOrderStatus::CONFIRMED,
                    'notes' => 'Order fully paid - automatically confirmed'
                ]);
            }

            DB::commit();

            return $payment->fresh(['salesOrder']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate unique order number
     * 
     * @return string
     */

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $today = now()->format('Ymd');

        $counter = SalesOrder::whereDate('created_at', now())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $today, $counter);
    }


    /**
     * Get order statistics
     *
     * @param array $filters
     * @return array
     */
    public function getOrderStats(array $filters = [])
    {
        $query = SalesOrder::query();

        // Apply filters
        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['sales_channel_id']) && $filters['sales_channel_id']) {
            $query->where('sales_channel_id', $filters['sales_channel_id']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('order_status_id', $filters['status']);
        }

        if (isset($filters['date_start']) && $filters['date_start']) {
            $query->whereDate('order_date', '>=', $filters['date_start']);
        }

        if (isset($filters['date_end']) && $filters['date_end']) {
            $query->whereDate('order_date', '<=', $filters['date_end']);
        }

        // Filter by payment method (through payments relation)
        if (isset($filters['payment_method_id']) && $filters['payment_method_id']) {
            $query->whereHas('payments', function ($pq) use ($filters) {
                $pq->where('payment_method_id', $filters['payment_method_id']);
            });
        }

        // All time totals (with filters)
        $countOrders = $query->clone()->count();
        $totalSales = $query->clone()->sum('total');
        $costProduct = $query->clone()->with('items')->get()->flatMap->items->sum(function ($item) {
            return $item->unit_cost * $item->quantity;
        });
        $netProfit = $totalSales - $costProduct;

        return [
            'count_orders' => $countOrders,
            'total_sales_amount' => (float) $totalSales,
            'total_profit_amount' => (float) $netProfit
        ];
    }
}

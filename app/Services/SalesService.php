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
            foreach ($data['items'] as $itemData) {
                $itemData['tax_rate'] = $order->tax_rate;
                $this->addOrderItem($order, $itemData);
            }

            $this->recalculateOrderTotals($order->id);

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
     * Update an existing sales order
     * 
     * @param int $orderId
     * @param array $data
     * @return SalesOrder
     * @throws Exception
     */
    public function updateSalesOrder(int $orderId, array $data): SalesOrder
    {
        DB::beginTransaction();

        try {
            $order = SalesOrder::with('items')->findOrFail($orderId);

            // Check if order can be edited (not confirmed, processing, shipped, or delivered)
            $editableStatuses = [EOrderStatus::PENDING];
            if (!in_array($order->orderStatus?->slug, $editableStatuses)) {
                throw new Exception('Only pending orders can be edited');
            }

            // Update order fields
            if (!empty($data['customer_id']))
                $order->customer_id = $data['customer_id'];

            if (!empty($data['sales_channel_id']))
                $order->sales_channel_id = $data['sales_channel_id'];

            if (!empty($data['warehouse_id']))
                $order->warehouse_id = $data['warehouse_id'];

            if (!empty($data['shipping_address_id']))
                $order->shipping_address_id = $data['shipping_address_id'];

            if (!empty($data['billing_address_id']))
                $order->billing_address_id = $data['billing_address_id'];

            if (!empty($data['shipping_method_id']))
                $order->shipping_method_id = $data['shipping_method_id'];

            if (!empty($data['discount_rule_id']))
                $order->discount_rule_id = $data['discount_rule_id'];

            if (!empty($data['tax_rate']))
                $order->tax_rate = $data['tax_rate'];

            if (!empty($data['discount_global']))
                $order->discount_global = $data['discount_global'];

            if (!empty($data['shipping']))
                $order->shipping = $data['shipping'];

            if (!empty($data['shipping_address']))
                $order->shipping_address = $data['shipping_address'];

            if (!empty($data['billing_address']))
                $order->billing_address = $data['billing_address'];

            if (!empty($data['notes']))
                $order->notes = $data['notes'];

            if (!empty($data['order_date']))
                $order->order_date = $data['order_date'];

            // Get existing item IDs
            $existingItemIds = $order->items->pluck('product_entry_id')->toArray();
            $newItemIds = collect($data['items'])->pluck('product_entry_id')->filter()->toArray();

            // Items to delete (exist in order but not in new data)
            $itemsToDelete = array_diff($existingItemIds, $newItemIds);

            // Delete removed items
            if (!empty($itemsToDelete)) {
                foreach ($itemsToDelete as $itemId) {
                    $orderItem = SalesOrderItem::where('product_entry_id', $itemId)
                        ->where('sales_order_id', $order->id)
                        ->first();
                    $this->cancelOrderItem($order, $orderItem, $orderItem->quantity);
                    $orderItem->delete();
                }
            }

            // Update or create items

            foreach ($data['items'] as $itemData) {
                $itemData['tax_rate'] = $order->tax_rate;

                // Calculate line totals for stats

                if (isset($itemData['product_entry_id']) && in_array($itemData['product_entry_id'], $existingItemIds)) {
                    // Update existing item
                    $item = SalesOrderItem::where('product_entry_id', $itemData['product_entry_id'])
                        ->where('sales_order_id', $order->id)
                        ->first();
                    $this->updateOrderItem($item, $itemData);
                } else {
                    // Create new item
                    $item = $this->addOrderItem($order, $itemData);
                }
            }

            $order->save();
            $this->recalculateOrderTotals($order->id);
            DB::commit();

            return $order->fresh(['items.product', 'items.productEntry', 'customer', 'salesChannel', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an order item
     * 
     * @param SalesOrderItem $item
     * @param array $data
     * @return SalesOrderItem
     */
    protected function updateOrderItem(SalesOrderItem $item, array $data): SalesOrderItem
    {
        $quantity = $data['quantity'];
        if ($item->quantity > $quantity) {
            // If reducing quantity, release the difference back to inventory
            $this->cancelOrderItem($item->salesOrder, $item, $item->quantity - $quantity);
        } else if ($item->quantity < $quantity) {
            // If increasing quantity, reserve the additional amount
            $newQuantity = $quantity - $item->quantity;
            $this->inventoryService->reserveInventory(
                $item->product_id,
                $item->warehouse_id,
                $newQuantity
            );

            $this->confirmOrderItem($item->salesOrder, $item, $newQuantity);
        }


        /*$item->product_id = $data['product_id'];
        $item->product_entry_id = $data['product_entry_id'] ?? null;*/
        $item->quantity = $quantity;
        $item->discount = $data['discount'] ?? 0;
        $item->tax_rate = $data['tax_rate'] ?? 1;

        // Calculate item totals
        $item->subtotal = round($item->quantity * $item->unit_price, 2);
        $item->subtotal_after_discount = round($item->subtotal - $item->discount, 2);
        $item->tax_amount = round($item->subtotal_after_discount * $item->tax_rate, 2);
        $item->total = $item->subtotal_after_discount + $item->tax_amount;

        $item->save();

        return $item;
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
        $productEntry = ProductEntry::findOrFail($data['product_entry_id']);

        // Reserve inventory
        $this->inventoryService->reserveInventory(
            $productEntry->product_id,
            $order->warehouse_id,
            $data['quantity']
        );

        $item = SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $productEntry->product_id,
            'product_entry_id' => $productEntry->id,
            'unit_price' => $productEntry->unit_price,
            'unit_cost' => $productEntry->unit_cost,
            'quantity' => $data['quantity'],
            'discount' => $data['discount'] ?? 0,
            'tax_rate' => $data['tax_rate'] ?? 1,
            'tax_amount' => 0,
            'subtotal' => 0,
            'subtotal_after_discount' => 0,
            'total' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        // Calculate item totals
        //$item->discount = $item->subtotal * ($item->discount / 100);
      
        $item->subtotal = round($item->quantity * $item->unit_price, 2);
        $item->subtotal_after_discount = round($item->subtotal - $item->discount, 2);
        $item->tax_amount = round($item->subtotal_after_discount * $item->tax_rate, 2);
        $item->total = $item->subtotal_after_discount + $item->tax_amount;

        $item->save();

        return $item;
    }

    /**
     * Delete an order item
     */
    public function deleteOrderItem(int $orderId, int $itemId)
    {
        $item = SalesOrderItem::where('id', $itemId)
            ->where('sales_order_id', $orderId)
            ->first();

        if (!$item) {
            throw new \Exception('Order item not found');
        }

        // Check if order is in editable status (pending)
        $order = $item->salesOrder()->with('orderStatus')->first();
        $currentStatus = $order->orderStatus->slug ?? '';

        if ($currentStatus !== EOrderStatus::PENDING) {
            throw new \Exception('Cannot delete items from orders that are not in pending status');
        }

        $this->cancelOrderItem($order, $item, $item->quantity);
        $item->delete();

        // Recalculate order totals
        $this->recalculateOrderTotals($orderId);

        return true;
    }

    /**
     * Recalculate order totals after item changes
     */
    protected function recalculateOrderTotals(int $orderId): void
    {
        $order = SalesOrder::with('items')->findOrFail($orderId);

        $subtotal = $order->items->sum('subtotal');
        $discountTotal = $order->items->sum('discount');
        $lineTaxAmountTotal = $order->items->sum('tax_amount');
        $discountGlobal = $order->discount_global ?? 0;

        $order->subtotal = $subtotal;
        $order->discount_total = $discountTotal + $discountGlobal;
        $order->tax_amount = $lineTaxAmountTotal;
        $order->subtotal_after_discount = $subtotal - $discountTotal - $discountGlobal;
        $order->total = $order->subtotal_after_discount + $lineTaxAmountTotal + ($order->shipping ?? 0);
        $order->save();
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
     * Confirm order and process inventory
     * 
     * @param SalesOrder $order
     * @return bool
     * @throws Exception
     */
    protected function confirmOrder($order): bool
    {
        // Validate that the order has all payments completed
        $totalPaid = $order->payments()
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalPaid < $order->total - 0.01) {
            throw new Exception('Cannot confirm order: payment not complete. Paid: $' . number_format($totalPaid, 2) . ' / Total: $' . number_format($order->total, 2));
        }

        // Process each item
        foreach ($order->items as $item) {
            $this->confirmOrderItem($order, $item, $item->quantity);
        }
        return true;
    }

    protected function confirmOrderItem($order, $item, $quantity)
    {
        // Release reservation
        $this->inventoryService->releaseReservedInventory(
            $item->product_id,
            $order->warehouse_id,
            $quantity
        );

        // Deduct from inventory
        $this->inventoryService->updateInventory(
            $item->product_id,
            $order->warehouse_id,
            $quantity,
            'subtract'
        );

        // Create stock movement
        $this->inventoryService->createStockMovement([
            'product_id' => $item->product_id,
            'warehouse_id' => $order->warehouse_id,
            'product_entry_id' => $item->product_entry_id,
            'type' => 'out',
            'quantity' => $quantity,
            'unit_price' => $item->unit_price,
            'reference_type' => 'sales_order',
            'reference_id' => $order->id,
            'notes' => 'Sale order: ' . $order->order_number,
            'created_by' => auth()->id(),
            'movement_date' => now(),
        ]);
    }
    /**
     * Cancel sales order and release inventory
     * 
     * @param SalesOrder $order
     * @return bool
     */
    protected function cancelOrder($order): bool
    {
        foreach ($order->items as $item) {
            $this->cancelOrderItem($order, $item, $item->quantity);
        }
        return true;
    }

    protected function cancelOrderItem($order, $item, $quantity)
    {
        $currentStatus = $order->orderStatus->slug ?? '';
        if ($currentStatus === EOrderStatus::PENDING) {
            $this->inventoryService->releaseReservedInventory(
                $item->product_id,
                $order->warehouse_id,
                $quantity
            );
        } else if (in_array($currentStatus, [EOrderStatus::CONFIRMED, EOrderStatus::PROCESSING, EOrderStatus::SHIPPED])) {
            // Return inventory for confirmed/processing orders
            $this->inventoryService->updateInventory(
                $item->product_id,
                $order->warehouse_id,
                $quantity,
                'add'
            );

            // Create stock movement for return
            $this->inventoryService->createStockMovement([
                'product_id' => $item->product_id,
                'warehouse_id' => $order->warehouse_id,
                'type' => 'in',
                'quantity' => $quantity,
                'reference_type' => 'sales_order_cancellation',
                'reference_id' => $order->id,
                'notes' => 'Order cancellation: ' . $order->order_number,
                'created_by' => auth()->id(),
                'movement_date' => now(),
            ]);
        }
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
            'total_cost_amount' => (float) $costProduct,
            'total_profit_amount' => (float) $netProfit
        ];
    }

    /**
     * Get valid status transitions for an order
     */
    public function getValidTransitions(int $orderId)
    {
        $order = SalesOrder::with('orderStatus')->findOrFail($orderId);

        $currentStatus = $order->orderStatus->slug ?? '';
        $validTransitions = EOrderStatus::getValidTransitions($currentStatus);

        // Get status details for valid transitions
        $validStatuses = OrderStatus::whereIn('slug', $validTransitions)->get(['id', 'name', 'slug', 'color']);

        return [
            'current_status' => $currentStatus,
            'valid_transitions' => $validStatuses
        ];
    }

     public function recent(int $limit = 10): array
    {
        $relations = ['customer', 'salesChannel', 'warehouse', 'orderStatus'];
        
        $orders = SalesOrder::with($relations)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $orders;
    }
}

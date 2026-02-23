<?php

namespace App\Services;

use App\Enums\EOrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Payment;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
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
            $order = SalesOrder::create([
                'order_number' => $data['order_number'],
                'customer_id' => $data['customer_id'] ?? null,
                'sales_channel_id' => $data['sales_channel_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_status_id' => $orderStatusId,
                'subtotal' => 0,
                'tax' => $data['tax'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'total' => 0,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'order_date' => $data['order_date'],
                'created_by' => $data['created_by'] ?? null,
            ]);

            // Add order items
            $subtotal = 0;
            foreach ($data['items'] as $itemData) {
                $item = $this->addOrderItem($order, $itemData);
                $subtotal += $item->subtotal;
            }

            // Update order totals
            $order->subtotal = $subtotal;
            $order->total = $subtotal + $order->tax + $order->shipping - $order->discount;
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
        // Reserve inventory
        $this->inventoryService->reserveInventory(
            $data['product_id'],
            $order->warehouse_id,
            $data['quantity']
        );

        // Calculate item totals
        $subtotal = $data['quantity'] * $data['unit_price'];
        $total = $subtotal - ($data['discount'] ?? 0) + ($data['tax'] ?? 0);

        return SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'product_id' => $data['product_id'],
            'product_entry_id' => $data['product_entry_id'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'unit_cost' => $data['unit_cost'] ?? null,
            'discount' => $data['discount'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'subtotal' => $subtotal,
            'total' => $total,
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
        $date = date('Ymd');
        $lastOrder = SalesOrder::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? (int)substr($lastOrder->order_number, -4) + 1 : 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
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
        $totalOrders = $query->clone()->count();
        $totalSales = $query->clone()->sum('total');

        // Calculate net profit (total - discount - tax + shipping - cost of items)
        // We'll use a simple calculation: total - discount - tax - shipping
        $totalDiscount = $query->clone()->sum('discount');
        $totalTax = $query->clone()->sum('tax');
        $totalShipping = $query->clone()->sum('shipping');
        $netProfit = $totalSales - $totalDiscount - $totalTax - $totalShipping;

        return [
            'total_orders' => $totalOrders,
            'total_sales' => (float) $totalSales,
            'net_profit' => (float) $netProfit
        ];
    }
}

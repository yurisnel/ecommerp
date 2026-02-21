<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Payment;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\StockMovement;
use App\Models\SalesChannel;
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
            $order = SalesOrder::create([
                'order_number' => $data['order_number'],
                'customer_id' => $data['customer_id'] ?? null,
                'sales_channel_id' => $data['sales_channel_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_status_id' => $data['order_status_id'] ?? null,
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

            // Create initial status history (if matching status exists)
            try {
                $statusSlug = $order->status;
                $statusModel = OrderStatus::where('slug', $statusSlug)->first();
                if ($statusModel) {
                    OrderStatusHistory::create([
                        'sales_order_id' => $order->id,
                        'order_status_id' => $statusModel->id,
                        'changed_by' => $order->created_by ?? auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Don't break order creation if history logging fails
            }

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
     * Confirm sales order and deduct inventory
     * 
     * @param int $orderId
     * @return SalesOrder
     * @throws Exception
     */
    public function confirmOrder(int $orderId): SalesOrder
    {
        DB::beginTransaction();
        
        try {
            $order = SalesOrder::with('items')->findOrFail($orderId);

            if ($order->status !== 'pending') {
                throw new Exception("Only pending orders can be confirmed");
            }

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

            // Update order status
            $old = $order->status;
            $order->status = 'confirmed';
            $order->save();

            // Log history
            try {
                $statusModel = OrderStatus::where('slug', $order->status)->first();
                if ($statusModel) {
                    OrderStatusHistory::create([
                        'sales_order_id' => $order->id,
                        'order_status_id' => $statusModel->id,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // ignore
            }

            DB::commit();
            
            return $order->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel sales order and release inventory
     * 
     * @param int $orderId
     * @return SalesOrder
     * @throws Exception
     */
    public function cancelOrder(int $orderId): SalesOrder
    {
        DB::beginTransaction();
        
        try {
            $order = SalesOrder::with('items')->findOrFail($orderId);

            if (in_array($order->status, ['delivered', 'cancelled'])) {
                throw new Exception("Cannot cancel {$order->status} orders");
            }

            // Release reserved inventory for pending orders
            if ($order->status === 'pending') {
                foreach ($order->items as $item) {
                    $this->inventoryService->releaseReservedInventory(
                        $item->product_id,
                        $order->warehouse_id,
                        $item->quantity
                    );
                }
            }

            // Return inventory for confirmed/processing orders
            if (in_array($order->status, ['confirmed', 'processing', 'shipped'])) {
                foreach ($order->items as $item) {
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

            // Update order status
            $old = $order->status;
            $order->status = 'cancelled';
            $order->save();

            // Log history
            try {
                $statusModel = OrderStatus::where('slug', $order->status)->first();
                if ($statusModel) {
                    OrderStatusHistory::create([
                        'sales_order_id' => $order->id,
                        'order_status_id' => $statusModel->id,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // ignore
            }

            DB::commit();
            
            return $order->fresh();
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

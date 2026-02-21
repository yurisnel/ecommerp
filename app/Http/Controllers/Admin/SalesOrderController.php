<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\SalesOrderService;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;

class SalesOrderController extends BaseController
{
    protected $salesService;

    public function __construct(SalesOrderService $service, SalesService $salesService)
    {
        parent::__construct($service);
        $this->salesService = $salesService;
    }

    /**
     * Display a listing of orders with relations
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only([
            'search', 
            'status', 
            'customer_id', 
            'warehouse_id',
            'sales_channel_id',
            'payment_method_id',
            'date_start',
            'date_end'
        ]);

        // In BaseController, it uses $this->service->search or paginate
        // We want to include relations by default for orders list
        $relations = ['customer', 'salesChannel', 'warehouse', 'payments.paymentMethod', 'orderStatus'];

        if ($perPage == -1) {
            $data = $this->service->getAll($relations);
        } else {
            $data = $this->service->search($filters, $perPage, $relations);
        }

        return $this->successResponse($data, 'Orders retrieved successfully');
    }

    /**
     * Get order statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = $request->only([
            'customer_id',
            'sales_channel_id',
            'payment_method_id',
            'status',
            'date_start',
            'date_end'
        ]);

        $stats = $this->salesService->getOrderStats($filters);

        return $this->successResponse($stats, 'Order statistics retrieved successfully');
    }

    /**
     * Display the specified order with items
     */
    public function show(string|int $id): JsonResponse
    {
        $id = (int) $id;
        $relations = ['customer', 'salesChannel', 'warehouse', 'items.product', 'items.productEntry', 'statusHistories.status', 'statusHistories.changer', 'payments.paymentMethod'];
        $order = $this->service->getById($id, $relations);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        return $this->successResponse($order, 'Order retrieved successfully');
    }

    /**
     * Create sales order (Delegated to SalesService for complex logic)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $order = $this->salesService->createSalesOrder($validated);

        return $this->successResponse($order, 'Sales order created successfully', 201);
    }

    /**
     * Confirm sales order
     */
    public function confirm(int $id): JsonResponse
    {
        $order = $this->salesService->confirmOrder($id);
        return $this->successResponse($order, 'Order confirmed successfully');
    }

    /**
     * Cancel sales order
     */
    public function cancel(int $id): JsonResponse
    {
        $order = $this->salesService->cancelOrder($id);
        return $this->successResponse($order, 'Order cancelled successfully');
    }

    /**
     * Add status history entry
     */
    public function addStatusHistory(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'order_status_id' => 'required|exists:order_statuses,id',
            'changed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = $this->service->getById($id);
        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $history = \App\Models\OrderStatusHistory::create([
            'sales_order_id' => $id,
            'order_status_id' => $validated['order_status_id'],
            'changed_by' => auth()->id() ?? 1,
            'changed_at' => $validated['changed_at'] ?? now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Also update the order's current status
        $order->order_status_id = $validated['order_status_id'];
        $order->save();

        $history->load('status');

        return $this->successResponse($history, 'Status history added successfully', 201);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'nullable|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ]);

        $payment = $this->salesService->processPayment($validated);
        return $this->successResponse($payment, 'Payment processed successfully', 201);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'sales_channel_id' => 'required|exists:sales_channels,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'shipping_address_id' => 'nullable|exists:customer_addresses,id',
            'billing_address_id' => 'nullable|exists:customer_addresses,id',
            'shipping_method_id' => 'nullable|exists:shipping_methods,id',
            'discount_rule_id' => 'nullable|exists:discount_rules,id',
            'order_status_id' => 'required|numeric',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_entry_id' => 'nullable|exists:product_entries,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
        ]);
    }

    /**
     * Update an order and record status history when changed
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRequest($request, $id);

        $order = $this->service->getById($id);
        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $oldStatus = $order->status;

        $result = $this->service->update($id, $validated);

        if (!$result) {
            return $this->errorResponse('Order not found', 404);
        }

        $order = $this->service->getById($id);

        // If status changed, log history
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            try {
                $statusModel = OrderStatus::where('slug', $validated['status'])->first();
                if ($statusModel) {
                    OrderStatusHistory::create([
                        'sales_order_id' => $order->id,
                        'order_status_id' => $statusModel->id,
                        'changed_by' => auth()->id(),
                        'changed_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // ignore logging failures
            }
        }

        return $this->successResponse($order, 'Order updated successfully');
    }
}

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
     * Add status history entry
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'order_status' => 'required|exists:order_statuses,slug',
            'changed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $history = $this->salesService->updateStatus($id, $validated);
        $history->load('status');

        return $this->successResponse($history, 'Status update successfully', 201);        
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
}

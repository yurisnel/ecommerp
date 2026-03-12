<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * List inventory summary
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'category_id', 'status']);

        $inventory = $this->inventoryService->getInventorySummary($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => __('api.inventory_summary_retrieved_successfully'),
            'data' => $inventory
        ]);
    }

    /**
     * Create product entry
     */
    public function createEntry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'base_cost' => 'required|numeric|min:0',
            'additional_costs_value' => 'nullable|numeric|min:0',
            'additional_costs_percent' => 'nullable|numeric|min:0|max:100',
            'unit_price' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        // Establecer valores por defecto para costos adicionales
        $validated['additional_costs_value'] = $validated['additional_costs_value'] ?? 0;
        $validated['additional_costs_percent'] = $validated['additional_costs_percent'] ?? 0;

        $entry = $this->inventoryService->createProductEntry($validated);

        return response()->json([
            'success' => true,
            'message' => __('api.product_entry_created_successfully'),
            'data' => $entry
        ], 201);
    }

    /**
     * Show a single product entry
     */
    public function showEntry(int $id): JsonResponse
    {
        $entry = $this->inventoryService->getProductEntry($id);

        if (!$entry) {
            return response()->json([
                'success' => false,
                'message' => __('api.product_entry_not_found')
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('api.product_entry_retrieved_successfully'),
            'data' => $entry
        ]);
    }

    /**
     * Update product entry
     */
    public function updateEntry(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'base_cost' => 'required|numeric|min:0',
            'additional_costs_value' => 'nullable|numeric|min:0',
            'additional_costs_percent' => 'nullable|numeric|min:0|max:100',
            'unit_price' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Establecer valores por defecto para costos adicionales
        $validated['additional_costs_value'] = $validated['additional_costs_value'] ?? 0;
        $validated['additional_costs_percent'] = $validated['additional_costs_percent'] ?? 0;

        $entry = $this->inventoryService->updateProductEntry($id, $validated);

        return response()->json([
            'success' => true,
            'message' => __('api.product_entry_updated_successfully'),
            'data' => $entry
        ]);
    }

    /**
     * Delete product entry
     */
    public function deleteEntry(int $id): JsonResponse
    {
        $this->inventoryService->deleteProductEntry($id);

        return response()->json([
            'success' => true,
            'message' => __('api.product_entry_deleted_successfully')
        ]);
    }

    /**
     * Adjust inventory
     */
    public function adjustInventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric', // Can be negative
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $movement = $this->inventoryService->adjustInventory($validated);

        return response()->json([
            'success' => true,
            'message' => __('api.inventory_adjusted_successfully'),
            'data' => $movement
        ]);
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $movement = $this->inventoryService->transferStock($validated);

        return response()->json([
            'success' => true,
            'message' => __('api.stock_transferred_successfully'),
            'data' => $movement
        ]);
    }

    /**
     * Get product inventory status
     */
    public function getProductStatus(int $productId): JsonResponse
    {
        $status = $this->inventoryService->getProductInventoryStatus($productId);

        return response()->json([
            'success' => true,
            'message' => __('api.product_status_retrieved_successfully'),
            'data' => $status
        ]);
    }
    /**
     * Get product entries history
     */
    public function getProductEntries(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'product_id', 'product_variant_id', 'warehouse_id', 'supplier_id', 'category_id', 'date_start', 'date_end']);

        $entries = $this->inventoryService->getProductEntries($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => __('api.product_entries_retrieved_successfully'),
            'data' => $entries
        ]);
    }

    /**
     * Get stock movements history
     */
    public function getStockMovements(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'type', 'product_id', 'product_variant_id', 'warehouse_id', 'category_id', 'supplier_id']);

        $movements = $this->inventoryService->getStockMovements($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => __('api.stock_movements_retrieved_successfully'),
            'data' => $movements
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'product_id', 'category_id', 'supplier_id']);
        $stats = $this->inventoryService->getInventoryStats($filters);

        return response()->json([
            'success' => true,
            'message' => __('api.inventory_statistics_retrieved_successfully'),
            'data' => $stats
        ]);
    }

    /**
     * Get low stock and out of stock alerts
     */
    public function getAlerts(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $alerts = $this->inventoryService->getStockAlerts($limit);

        return response()->json([
            'success' => true,
            'message' => __('api.stock_alerts_retrieved_successfully'),
            'data' => $alerts
        ]);
    }
}

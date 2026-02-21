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
            'message' => 'Inventory summary retrieved successfully',
            'data' => $inventory
        ]);
    }

    /**
     * Create product entry
     */
    public function createEntry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_number' => 'nullable|string|max:50|unique:product_entries',
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'cost_per_unit' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $entry = $this->inventoryService->createProductEntry($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product entry created successfully',
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
                'message' => 'Product entry not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product entry retrieved successfully',
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
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'cost_per_unit' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'entry_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $entry = $this->inventoryService->updateProductEntry($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Product entry updated successfully',
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
            'message' => 'Product entry deleted successfully'
        ]);
    }

    /**
     * Adjust inventory
     */
    public function adjustInventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric', // Can be negative
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $movement = $this->inventoryService->adjustInventory($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventory adjusted successfully',
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
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $movement = $this->inventoryService->transferStock($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock transferred successfully',
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
            'message' => 'Product status retrieved successfully',
            'data' => $status
        ]);
    }
    /**
     * Get product entries history
     */
    public function getProductEntries(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'product_id', 'warehouse_id', 'supplier_id']);

        $entries = $this->inventoryService->getProductEntries($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Product entries retrieved successfully',
            'data' => $entries
        ]);
    }

    /**
     * Get stock movements history
     */
    public function getStockMovements(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'type', 'product_id', 'warehouse_id', 'category_id', 'supplier_id']);

        $movements = $this->inventoryService->getStockMovements($perPage, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Stock movements retrieved successfully',
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
            'message' => 'Inventory statistics retrieved successfully',
            'data' => $stats
        ]);
    }
}

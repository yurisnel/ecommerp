<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductVariantController extends Controller
{
    protected $variantService;

    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * List all variants
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'product_id', 'is_active']);

        $variants = $this->variantService->search($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Variants retrieved successfully',
            'data' => $variants
        ]);
    }

    /**
     * Get variants by product ID
     */
    public function byProduct(int $productId): JsonResponse
    {
        $variants = $this->variantService->getByProductId($productId, [
            'attributeValues',
            'attributeValues.attribute',
            'inventory',
            'product'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product variants retrieved successfully',
            'data' => $variants
        ]);
    }

    /**
     * Show a single variant
     */
    public function show(int $id): JsonResponse
    {
        $variant = $this->variantService->getById($id, [
            'product',
            'attributeValues',
            'attributeValues.attribute',
            'stocks',
            'stocks.warehouse'
        ]);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Variant retrieved successfully',
            'data' => $variant
        ]);
    }

    /**
     * Create a new variant
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku' => 'required|string|max:100|unique:product_variants,sku',
            'name' => 'required|string|max:200',
            'barcode' => 'nullable|string|max:100|unique:product_variants,barcode',
            'weight' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $variant = $this->variantService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant created successfully',
            'data' => $variant
        ], 201);
    }

    /**
     * Update a variant
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'sometimes|string|max:100|unique:product_variants,sku,' . $id,
            'name' => 'sometimes|string|max:200',
            'barcode' => 'nullable|string|max:100|unique:product_variants,barcode,' . $id,
            'weight' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $variant = $this->variantService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Variant updated successfully',
            'data' => $variant
        ]);
    }

    /**
     * Delete a variant
     */
    public function destroy(int $id): JsonResponse
    {
        $this->variantService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully'
        ]);
    }

    // ===== STOCK MANAGEMENT =====

    /**
     * Get variant stock
     */
    public function stocks(int $variantId): JsonResponse
    {
        $stocks = $this->variantService->getStocks($variantId);

        return response()->json([
            'success' => true,
            'message' => 'Stocks retrieved successfully',
            'data' => $stocks
        ]);
    }

    /**
     * Get variant inventory status
     */
    public function inventoryStatus(int $variantId): JsonResponse
    {
        $status = $this->variantService->getInventoryStatus($variantId);

        return response()->json([
            'success' => true,
            'message' => 'Inventory status retrieved successfully',
            'data' => $status
        ]);
    }

    /**
     * Add stock to variant
     */
    public function addStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'product_entry_id' => 'nullable|exists:product_entries,id',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $stock = $this->variantService->addStock($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully',
            'data' => $stock
        ], 201);
    }

    /**
     * Remove stock from variant
     */
    public function removeStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $stock = $this->variantService->removeStock($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock removed successfully',
            'data' => $stock
        ]);
    }

    /**
     * Adjust variant stock
     */
    public function adjustStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $stock = $this->variantService->adjustStock($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'data' => $stock
        ]);
    }

    /**
     * Transfer variant stock between warehouses
     */
    public function transferStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $result = $this->variantService->transferStock($validated);

        return response()->json([
            'success' => true,
            'message' => 'Stock transferred successfully',
            'data' => $result
        ]);
    }

    /**
     * Get stock movements for variant
     */
    public function stockMovements(Request $request, int $variantId): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $movements = $this->variantService->getStockMovements($variantId, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Stock movements retrieved successfully',
            'data' => $movements
        ]);
    }

    /**
     * Generate variants from attribute combinations
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'attribute_values' => 'required|array|min:1',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        $variants = $this->variantService->generateVariants(
            $validated['product_id'],
            $validated['attribute_values']
        );

        return response()->json([
            'success' => true,
            'message' => 'Variants generated successfully',
            'data' => $variants
        ]);
    }
}

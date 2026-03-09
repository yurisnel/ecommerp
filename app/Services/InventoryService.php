<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Inventory Service
 * Manages inventory operations, stock movements, and product entries
 */
class InventoryService
{
    /**
     * Create a new product entry and update inventory
     * 
     * @param array $data
     * @return ProductEntry
     * @throws Exception
     */
    public function createProductEntry(array $data): ProductEntry
    {
        DB::beginTransaction();

        try {
            // Calculate and store unit_cost before saving
            $baseCost = $data['base_cost'] ?? 0;
            $additionalValue = $data['additional_costs_value'] ?? 0;
            $additionalPercent = $data['additional_costs_percent'] ?? 0;
            $additionalFromPercent = $baseCost * ($additionalPercent / 100);
            $data['unit_cost'] = $baseCost + $additionalValue + $additionalFromPercent;

            // Create product entry
            $entry = ProductEntry::create($data);

            // Create stock movement (IN) - use stored unit cost
            $this->createStockMovement([
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'product_entry_id' => $entry->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'],
                'unit_price' => $entry->unit_price,
                'reference_type' => 'product_entry',
                'reference_id' => $entry->id,
                'notes' => '',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => $data['entry_date'],
            ]);

            // Update inventory
            $this->updateInventory(
                $data['product_id'],
                $data['warehouse_id'],
                $data['quantity'],
                'add',
                $data['product_variant_id'] ?? null
            );

            DB::commit();

            return $entry->fresh(['product', 'variant', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get a single product entry
     * 
     * @param int $id
     * @return ProductEntry
     */
    public function getProductEntry(int $id): ProductEntry
    {
        return ProductEntry::with(['product', 'product.variants', 'variant', 'supplier', 'warehouse', 'creator'])->findOrFail($id);
    }

    /**
     * Update an existing product entry and adjust inventory
     * 
     * @param int $id
     * @param array $data
     * @return ProductEntry
     * @throws Exception
     */
    public function updateProductEntry(int $id, array $data): ProductEntry
    {
        DB::beginTransaction();

        try {
            // Calculate and store unit_cost before updating
            $baseCost = $data['base_cost'] ?? 0;
            $additionalValue = $data['additional_costs_value'] ?? 0;
            $additionalPercent = $data['additional_costs_percent'] ?? 0;
            $additionalFromPercent = $baseCost * ($additionalPercent / 100);
            $data['unit_cost'] = $baseCost + $additionalValue + $additionalFromPercent;

            $entry = ProductEntry::findOrFail($id);
            $oldQuantity = $entry->quantity;
            $oldWarehouseId = $entry->warehouse_id;
            $oldProductId = $entry->product_id;
            $oldVariantId = $entry->product_variant_id;
            $entry->update($data);

            // If quantity, product, variant or warehouse changed, we need to adjust inventory
            if ($oldQuantity != $entry->quantity || $oldWarehouseId != $entry->warehouse_id || $oldProductId != $entry->product_id || $oldVariantId != $entry->product_variant_id) {
                // 1. Revert old inventory
                $this->updateInventory($oldProductId, $oldWarehouseId, $oldQuantity, 'subtract', $oldVariantId);

                // 2. Apply new inventory
                $this->updateInventory($entry->product_id, $entry->warehouse_id, $entry->quantity, 'add', $entry->product_variant_id);
            }

            // Update associated stock movement
            $movement = StockMovement::where('product_entry_id', $entry->id)
                ->where('type', 'in')
                ->first();

            if ($movement) {
                $movement->update([
                    'product_id' => $entry->product_id,
                    'product_variant_id' => $entry->product_variant_id,
                    'warehouse_id' => $entry->warehouse_id,
                    'quantity' => $entry->quantity,
                    'unit_cost' => $entry->unit_cost, // Stored calculated value
                    'unit_price' => $entry->unit_price,
                    'notes' => $entry->notes ?? 'Updated Product entry #' . $entry->id,
                    'movement_date' => $entry->entry_date,
                ]);
            }

            DB::commit();

            return $entry->fresh(['product', 'variant', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a product entry and revert inventory
     * 
     * @param int $id
     * @throws Exception
     */
    public function deleteProductEntry(int $id): void
    {
        DB::beginTransaction();

        try {
            $entry = ProductEntry::findOrFail($id);

            // Revert inventory
            // First check if we have enough stock to revert
            $inventory = Inventory::where('product_id', $entry->product_id)
                ->where('warehouse_id', $entry->warehouse_id)
                ->where('product_variant_id', $entry->product_variant_id)
                ->first();

            if (!$inventory || $inventory->available_quantity < $entry->quantity) {
                throw new Exception("Cannot delete entry: Insufficient stock in warehouse to revert the quantity.");
            }

            $this->updateInventory($entry->product_id, $entry->warehouse_id, $entry->quantity, 'subtract', $entry->product_variant_id);

            // Delete associated stock movements
            StockMovement::where('product_entry_id', $entry->id)->delete();

            // Delete the entry
            $entry->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create stock movement
     * 
     * @param array $data
     * @return StockMovement
     */
    public function createStockMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    /**
     * Update inventory quantity
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @param string $operation (add|subtract)
     * @param int|null $variantId
     * @return Inventory
     */
    public function updateInventory(int $productId, int $warehouseId, float $quantity, string $operation = 'add', ?int $variantId = null): Inventory
    {
        $inventory = Inventory::firstOrCreate(
            [
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
            ]
        );

        if ($operation === 'add') {
            $inventory->quantity += $quantity;
        } else {
            $inventory->quantity -= $quantity;
        }

        $inventory->updateAvailableQuantity();

        return $inventory;
    }

    /**
     * Reserve inventory for an order
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @return Inventory
     * @throws Exception
     */
    public function reserveInventory(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): Inventory
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_variant_id', $variantId)
            ->first();

        if (!$inventory || $inventory->available_quantity < $quantity) {
            $message = "Insufficient inventory for product ID {$productId}";
            if ($variantId) {
                $message .= " (Variant ID {$variantId})";
            }
            throw new Exception($message);
        }

        $inventory->reserved_quantity += $quantity;
        $inventory->updateAvailableQuantity();

        return $inventory;
    }

    /**
     * Release reserved inventory
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @return Inventory
     */
    public function releaseReservedInventory(int $productId, int $warehouseId, float $quantity, ?int $variantId = null): Inventory
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($inventory) {
            $inventory->reserved_quantity -= $quantity;
            $inventory->updateAvailableQuantity();
        }

        return $inventory;
    }

    /**
     * Adjust inventory (for corrections)
     * 
     * @param array $data
     * @return StockMovement
     * @throws Exception
     */
    public function adjustInventory(array $data): StockMovement
    {
        DB::beginTransaction();

        try {
            $movement = $this->createStockMovement([
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'adjustment',
                'quantity' => abs($data['quantity']),
                'notes' => $data['notes'] ?? 'Inventory adjustment',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            $operation = $data['quantity'] > 0 ? 'add' : 'subtract';
            $this->updateInventory(
                $data['product_id'],
                $data['warehouse_id'],
                abs($data['quantity']),
                $operation,
                $data['product_variant_id'] ?? null
            );

            DB::commit();

            return $movement;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Transfer stock between warehouses
     * 
     * @param array $data
     * @return StockMovement
     * @throws Exception
     */
    public function transferStock(array $data): StockMovement
    {
        DB::beginTransaction();

        try {
            // Check if source warehouse has enough stock
            $sourceInventory = Inventory::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['from_warehouse_id'])
                ->where('product_variant_id', $data['product_variant_id'] ?? null)
                ->first();

            if (!$sourceInventory || $sourceInventory->available_quantity < $data['quantity']) {
                throw new Exception("Insufficient inventory in source warehouse");
            }

            // Create transfer movement
            $movement = $this->createStockMovement([
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'warehouse_id' => $data['from_warehouse_id'],
                'type' => 'transfer',
                'quantity' => $data['quantity'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'notes' => $data['notes'] ?? 'Stock transfer',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            // Subtract from source warehouse
            $this->updateInventory(
                $data['product_id'],
                $data['from_warehouse_id'],
                $data['quantity'],
                'subtract',
                $data['product_variant_id'] ?? null
            );

            // Add to destination warehouse
            $this->updateInventory(
                $data['product_id'],
                $data['to_warehouse_id'],
                $data['quantity'],
                'add',
                $data['product_variant_id'] ?? null
            );

            DB::commit();

            return $movement;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get inventory status for a product
     * 
     * @param int $productId
     * @return array
     */
    public function getProductInventoryStatus(int $productId): array
    {
        $product = Product::with(['inventory.warehouse', 'inventory.variant', 'defaultImage', 'latestEntry', 'variants'])->findOrFail($productId);

        $totalQuantity = $product->inventory->sum('quantity');
        $totalReserved = $product->inventory->sum('reserved_quantity');
        $totalAvailable = $product->inventory->sum('available_quantity');

        return [
            'product' => $product,
            'total_quantity' => $totalQuantity,
            'total_reserved' => $totalReserved,
            'total_available' => $totalAvailable,
            'warehouses' => $product->inventory,
            'is_below_minimum' => $totalQuantity < $product->min_stock,
        ];
    }

    /**
     * Get inventory summary (paginated)
     * 
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getInventorySummary(int $perPage = 15, array $filters = [])
    {
        $query = Product::with(['categories', 'inventory.warehouse', 'latestEntry', 'defaultImage'])
            ->withSum('inventory as total_quantity', 'quantity')
            ->withSum('inventory as total_reserved', 'reserved_quantity')
            ->withSum('inventory as total_available', 'available_quantity');

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $categoryIds = is_array($filters['category_id']) ? $filters['category_id'] : [$filters['category_id']];

            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('category_product.category_id', $categoryIds);
            });
        }

        // Filter by stock status
        if (isset($filters['status']) && $filters['status']) {
            if ($filters['status'] === 'low_stock') {
                $query->whereRaw('(SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) < min_stock');
            } elseif ($filters['status'] === 'out_of_stock') {
                $query->whereRaw('(SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) <= 0');
            } elseif ($filters['status'] === 'in_stock') {
                $query->whereRaw('(SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) > 0');
            }
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Get inventory statistics for dashboard
     * 
     * @param array $filters
     * @return array
     */
    public function getInventoryStats(array $filters = []): array
    {
        // Basic stats from products table (always global)
        $totalProducts = Product::count();
        $lowStock = Product::whereRaw(
            '(SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) < COALESCE(min_stock, 10)'
        )->count();
        $outOfStock = Product::whereRaw(
            '(SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) <= 0 OR (SELECT SUM(quantity) FROM inventories WHERE product_id = products.id) IS NULL'
        )->count();

        // Stats from product entries with filters
        $query = ProductEntry::query();

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['product_id']) && $filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['supplier_id']) && $filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query->whereHas('product', function ($pq) use ($filters) {
                $pq->whereHas('categories', function ($cq) use ($filters) {
                    $cq->where('category_product.category_id', $filters['category_id']);
                });
            });
        }

        $totalQuantity = $query->sum(DB::raw('COALESCE(quantity, 0)'));
        // Use stored unit_cost for better performance
        $totalInvested = $query->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(unit_cost, 0)'));
        $totalSellingPrice = $query->sum(DB::raw('COALESCE(quantity, 0) * COALESCE(unit_price, 0)'));
        $totalProfit = $totalSellingPrice - $totalInvested;

        return [
            'total_products' => $totalProducts,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_quantity' => (float) $totalQuantity,
            'total_invested' => (float) $totalInvested,
            'total_profit' => (float) $totalProfit,
            'total_value' => (float) $totalSellingPrice
        ];
    }

    /**
     * Get product entries history (paginated)
     * 
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProductEntries(int $perPage = 15, array $filters = [])
    {
        $query = ProductEntry::with(['product', 'product.defaultImage', 'variant', 'supplier', 'warehouse'])->orderBy('id', 'desc');

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['product_variant_id'])) {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Filter by product category
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query->whereHas('product', function ($pq) use ($filters) {
                $pq->whereHas('categories', function ($cq) use ($filters) {
                    $cq->where('category_product.category_id', $filters['category_id']);
                });
            });
        }

        // Filter by date range
        if (isset($filters['date_start']) && !empty($filters['date_start'])) {
            $query->whereDate('entry_date', '>=', $filters['date_start']);
        }

        if (isset($filters['date_end']) && !empty($filters['date_end'])) {
            $query->whereDate('entry_date', '<=', $filters['date_end']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get stock movements history (paginated)
     * 
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getStockMovements(int $perPage = 15, array $filters = [])
    {
        $query = StockMovement::with(['product', 'product.defaultImage', 'variant', 'warehouse', 'fromWarehouse', 'toWarehouse'])->orderBy('id', 'desc');

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['product_variant_id'])) {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['category_id']) && $filters['category_id']) {
            $query->whereHas('product', function ($pq) use ($filters) {
                $pq->where('category_id', $filters['category_id']);
            });
        }

        if (isset($filters['supplier_id']) && $filters['supplier_id']) {
            $query->whereHas('product', function ($pq) use ($filters) {
                $pq->where('supplier_id', $filters['supplier_id']);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get stock alerts (low stock and out of stock products)
     * 
     * @param int $limit
     * @return array
     */
    public function getStockAlerts(int $limit = 10): array
    {
        // Get products with their current inventory
        $products = Product::with(['inventory', 'category'])
            ->get()
            ->map(function ($product) {
                $totalQuantity = $product->inventory->sum('available_quantity');
                $product->total_quantity = $totalQuantity;
                return $product;
            });

        // Out of stock (quantity = 0)
        $outOfStock = $products
            ->filter(fn($p) => $p->total_quantity <= 0)
            ->take($limit)
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'type' => 'out_of_stock',
                    'severity' => 'critical',
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'category' => $product->category?->name,
                    'current_quantity' => 0,
                    'message' => 'Producto sin stock',
                    'created_at' => now()->toISOString(),
                ];
            })
            ->values();

        // Low stock (quantity > 0 but <= min_stock)
        $lowStock = $products
            ->filter(fn($p) => $p->total_quantity > 0 && $p->total_quantity <= ($p->min_stock ?? 10))
            ->take($limit)
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'type' => 'low_stock',
                    'severity' => 'warning',
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'category' => $product->category?->name,
                    'current_quantity' => $product->total_quantity,
                    'min_stock' => $product->min_stock ?? 10,
                    'message' => 'Stock bajo: ' . $product->total_quantity . ' unidades',
                    'created_at' => now()->toISOString(),
                ];
            })
            ->values();

        return [
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'total_critical' => $outOfStock->count(),
            'total_warning' => $lowStock->count(),
        ];
    }
}

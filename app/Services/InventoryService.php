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
           
            // Create product entry
            $entry = ProductEntry::create($data);

            // Create stock movement (IN)
            $this->createStockMovement([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'product_entry_id' => $entry->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_cost'],
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
                'add'
            );

            DB::commit();

            return $entry->fresh(['product', 'supplier', 'warehouse']);
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
        return ProductEntry::with(['product', 'supplier', 'warehouse', 'creator'])->findOrFail($id);
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
            $entry = ProductEntry::findOrFail($id);
            $oldQuantity = $entry->quantity;
            $oldWarehouseId = $entry->warehouse_id;
            $oldProductId = $entry->product_id;
            $entry->update($data);

            // If quantity, product or warehouse changed, we need to adjust inventory
            if ($oldQuantity != $entry->quantity || $oldWarehouseId != $entry->warehouse_id || $oldProductId != $entry->product_id) {
                // 1. Revert old inventory
                $this->updateInventory($oldProductId, $oldWarehouseId, $oldQuantity, 'subtract');

                // 2. Apply new inventory
                $this->updateInventory($entry->product_id, $entry->warehouse_id, $entry->quantity, 'add');
            }

            // Update associated stock movement
            $movement = StockMovement::where('product_entry_id', $entry->id)
                ->where('type', 'in')
                ->first();

            if ($movement) {
                $movement->update([
                    'product_id' => $entry->product_id,
                    'warehouse_id' => $entry->warehouse_id,
                    'quantity' => $entry->quantity,
                    'unit_price' => $entry->unit_cost,
                    'notes' => $entry->notes ?? 'Updated Product entry #' . $entry->id,
                    'movement_date' => $entry->entry_date,
                ]);
            }

            DB::commit();

            return $entry->fresh(['product', 'supplier', 'warehouse']);
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
                ->first();

            if (!$inventory || $inventory->available_quantity < $entry->quantity) {
                throw new Exception("Cannot delete entry: Insufficient stock in warehouse to revert the quantity.");
            }

            $this->updateInventory($entry->product_id, $entry->warehouse_id, $entry->quantity, 'subtract');

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
     * @return Inventory
     */
    public function updateInventory(int $productId, int $warehouseId, float $quantity, string $operation = 'add'): Inventory
    {
        $inventory = Inventory::firstOrCreate(
            [
                'product_id' => $productId,
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
    public function reserveInventory(int $productId, int $warehouseId, float $quantity): Inventory
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$inventory || $inventory->available_quantity < $quantity) {
            throw new Exception("Insufficient inventory for product ID {$productId}");
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
    public function releaseReservedInventory(int $productId, int $warehouseId, float $quantity): Inventory
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
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
                $operation
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
                ->first();

            if (!$sourceInventory || $sourceInventory->available_quantity < $data['quantity']) {
                throw new Exception("Insufficient inventory in source warehouse");
            }

            // Create transfer movement
            $movement = $this->createStockMovement([
                'product_id' => $data['product_id'],
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
                'subtract'
            );

            // Add to destination warehouse
            $this->updateInventory(
                $data['product_id'],
                $data['to_warehouse_id'],
                $data['quantity'],
                'add'
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
        $product = Product::with(['inventory.warehouse', 'defaultImage', 'latestEntry'])->findOrFail($productId);

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
        $query = ProductEntry::with(['product', 'product.defaultImage', 'supplier', 'warehouse'])->orderBy('id', 'desc');

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
        $query = StockMovement::with(['product','product.defaultImage', 'warehouse', 'fromWarehouse', 'toWarehouse'])->orderBy('id', 'desc');

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
}


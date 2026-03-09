<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\AttributeValue;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Product Variant Service
 * Manages product variants and their stock using existing tables
 */
class ProductVariantService
{
    /**
     * Get all variants with pagination
     */
    public function getAll(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return ProductVariant::with($relations)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get variants by product ID
     */
    public function getByProductId(int $productId, array $relations = []): \Illuminate\Database\Eloquent\Collection
    {
        return ProductVariant::with($relations)
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get variant by ID
     */
    public function getById(int $id, array $relations = []): ?ProductVariant
    {
        return ProductVariant::with($relations)->find($id);
    }

    /**
     * Get variant by SKU
     */
    public function getBySku(string $sku): ?ProductVariant
    {
        return ProductVariant::where('sku', $sku)->first();
    }

    /**
     * Create a new variant
     */
    public function create(array $data): ProductVariant
    {
        return DB::transaction(function () use ($data) {
            $variant = ProductVariant::create([
                'product_id' => $data['product_id'],
                'sku' => $data['sku'],
                'name' => $data['name'],
                'barcode' => $data['barcode'] ?? null,
                'weight' => $data['weight'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            // Attach attribute values if provided
            if (isset($data['attribute_values']) && is_array($data['attribute_values'])) {
                $syncData = $this->buildAttributeValueSyncData($data['attribute_values']);
                $variant->attributeValues()->sync($syncData);
            }

            return $variant->fresh(['product', 'attributeValues', 'attributeValues.attribute']);
        });
    }

    /**
     * Update a variant
     */
    public function update(int $id, array $data): ProductVariant
    {
        return DB::transaction(function () use ($id, $data) {
            $variant = ProductVariant::findOrFail($id);

            $variant->update([
                'sku' => $data['sku'] ?? $variant->sku,
                'name' => $data['name'] ?? $variant->name,
                'barcode' => $data['barcode'] ?? $variant->barcode,
                'weight' => $data['weight'] ?? $variant->weight,
                'image_url' => $data['image_url'] ?? $variant->image_url,
                'description' => $data['description'] ?? $variant->description,
                'is_active' => $data['is_active'] ?? $variant->is_active,
                'sort_order' => $data['sort_order'] ?? $variant->sort_order,
            ]);

            // Update attribute values if provided
            if (isset($data['attribute_values'])) {
                $syncData = $this->buildAttributeValueSyncData($data['attribute_values']);
                $variant->attributeValues()->sync($syncData);
            }

            return $variant->fresh(['product', 'attributeValues', 'attributeValues.attribute']);
        });
    }

    /**
     * Delete a variant
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $variant = ProductVariant::findOrFail($id);
            
            // Delete attribute values
            $variant->attributeValues()->detach();
            
            // Delete the variant (inventory and movements keep the reference)
            $variant->delete();
        });
    }

    /**
     * Search variants
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductVariant::with(['product', 'attributeValues']);

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (isset($filters['product_id']) && $filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    // ===== STOCK MANAGEMENT (Using existing tables) =====

    /**
     * Get stock for a variant in a specific warehouse
     */
    public function getStock(int $variantId, int $warehouseId): ?Inventory
    {
        return Inventory::where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    /**
     * Get all stocks for a variant
     */
    public function getStocks(int $variantId): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::where('product_variant_id', $variantId)
            ->with('warehouse')
            ->get();
    }

    /**
     * Add stock to a variant using existing inventory service
     */
    public function addStock(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::firstOrCreate(
                [
                    'product_variant_id' => $data['product_variant_id'],
                    'warehouse_id' => $data['warehouse_id'],
                ],
                [
                    'product_id' => null, // Will be set from variant
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            // Set product_id from variant
            $variant = ProductVariant::find($data['product_variant_id']);
            if ($variant) {
                $inventory->product_id = $variant->product_id;
            }

            $inventory->quantity += $data['quantity'];
            $inventory->updateAvailableQuantity();
            $inventory->save();

            // Create stock movement
            StockMovement::create([
                'product_id' => $variant?->product_id,
                'product_variant_id' => $data['product_variant_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'in',
                'quantity' => $data['quantity'],
                'unit_cost' => $data['unit_cost'] ?? null,
                'unit_price' => $data['unit_price'] ?? null,
                'product_entry_id' => $data['product_entry_id'] ?? null,
                'notes' => $data['notes'] ?? 'Stock added to variant',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            return $inventory->fresh('warehouse');
        });
    }

    /**
     * Remove stock from a variant
     */
    public function removeStock(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::where('product_variant_id', $data['product_variant_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->firstOrFail();

            if ($inventory->available_quantity < $data['quantity']) {
                throw new Exception("Insufficient stock available");
            }

            $inventory->quantity -= $data['quantity'];
            $inventory->updateAvailableQuantity();
            $inventory->save();

            // Create stock movement
            $variant = ProductVariant::find($data['product_variant_id']);
            StockMovement::create([
                'product_id' => $variant?->product_id,
                'product_variant_id' => $data['product_variant_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'out',
                'quantity' => $data['quantity'],
                'notes' => $data['notes'] ?? 'Stock removed from variant',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            return $inventory->fresh('warehouse');
        });
    }

    /**
     * Adjust stock (can be positive or negative)
     */
    public function adjustStock(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::firstOrCreate(
                [
                    'product_variant_id' => $data['product_variant_id'],
                    'warehouse_id' => $data['warehouse_id'],
                ],
                [
                    'product_id' => null,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );

            $variant = ProductVariant::find($data['product_variant_id']);
            if ($variant && !$inventory->product_id) {
                $inventory->product_id = $variant->product_id;
            }

            $quantity = abs($data['quantity']);
            $operation = $data['quantity'] > 0 ? 'add' : 'subtract';

            if ($operation === 'add') {
                $inventory->quantity += $quantity;
            } else {
                if ($inventory->available_quantity < $quantity) {
                    throw new Exception("Insufficient stock to adjust");
                }
                $inventory->quantity -= $quantity;
            }

            $inventory->updateAvailableQuantity();
            $inventory->save();

            // Create stock movement
            StockMovement::create([
                'product_id' => $variant?->product_id,
                'product_variant_id' => $data['product_variant_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => 'adjustment',
                'quantity' => $quantity,
                'notes' => $data['notes'] ?? 'Stock adjustment',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            return $inventory->fresh('warehouse');
        });
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Check source stock
            $sourceInventory = Inventory::where('product_variant_id', $data['product_variant_id'])
                ->where('warehouse_id', $data['from_warehouse_id'])
                ->first();

            if (!$sourceInventory || $sourceInventory->available_quantity < $data['quantity']) {
                throw new Exception("Insufficient stock in source warehouse");
            }

            $variant = ProductVariant::find($data['product_variant_id']);

            // Remove from source
            $sourceInventory->quantity -= $data['quantity'];
            $sourceInventory->updateAvailableQuantity();
            $sourceInventory->save();

            // Add to destination
            $destInventory = Inventory::firstOrCreate(
                [
                    'product_variant_id' => $data['product_variant_id'],
                    'warehouse_id' => $data['to_warehouse_id'],
                ],
                [
                    'product_id' => $variant?->product_id,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                ]
            );
            $destInventory->quantity += $data['quantity'];
            $destInventory->updateAvailableQuantity();
            $destInventory->save();

            // Create stock movement
            StockMovement::create([
                'product_id' => $variant?->product_id,
                'product_variant_id' => $data['product_variant_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'type' => 'transfer',
                'quantity' => $data['quantity'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'notes' => $data['notes'] ?? 'Stock transfer',
                'created_by' => $data['created_by'] ?? null,
                'movement_date' => now(),
            ]);

            return [
                'source' => $sourceInventory->fresh('warehouse'),
                'destination' => $destInventory->fresh('warehouse'),
            ];
        });
    }

    /**
     * Get stock movements for a variant
     */
    public function getStockMovements(int $variantId, int $perPage = 15): LengthAwarePaginator
    {
        return StockMovement::where('product_variant_id', $variantId)
            ->with(['warehouse', 'creator'])
            ->orderBy('movement_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get variant inventory status
     */
    public function getInventoryStatus(int $variantId): array
    {
        $variant = ProductVariant::with(['product', 'inventory.warehouse', 'attributeValues'])
            ->findOrFail($variantId);

        $totalQuantity = $variant->inventory->sum('quantity');
        $totalReserved = $variant->inventory->sum('reserved_quantity');
        $totalAvailable = $variant->inventory->sum('available_quantity');

        return [
            'variant' => $variant,
            'total_quantity' => $totalQuantity,
            'total_reserved' => $totalReserved,
            'total_available' => $totalAvailable,
            'warehouses' => $variant->inventory,
        ];
    }

    /**
     * Generate variants from attribute combinations
     */
    public function generateVariants(int $productId, array $attributeValueIds): array
    {
        $product = Product::findOrFail($productId);
        
        $variants = [];
        
        // Get all attribute values
        $attributeValues = \App\Models\AttributeValue::whereIn('id', $attributeValueIds)
            ->with('attribute')
            ->get()
            ->groupBy('attribute_id');

        // Generate all combinations
        $combinations = $this->generateCombinations($attributeValues->toArray());
        
        foreach ($combinations as $combination) {
            $variantName = $product->name . ' - ' . implode(', ', array_column($combination, 'value'));
            $variantSku = $product->sku . '-' . implode('-', array_column($combination, 'id'));
            
            $variants[] = [
                'product_id' => $productId,
                'sku' => strtoupper($variantSku),
                'name' => $variantName,
                'attribute_values' => array_column($combination, 'id'),
            ];
        }

        return $variants;
    }

    /**
     * Generate combinations from attribute values
     */
    private function generateCombinations(array $attributeValues): array
    {
        $result = [[]];
        
        foreach ($attributeValues as $attributeId => $values) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($values as $value) {
                    $newResult[] = array_merge($existing, [$value->toArray()]);
                }
            }
            $result = $newResult;
        }
        
        return $result;
    }
    /**
     * Build sync data array for attribute values, including attribute_id in the pivot.
     * The pivot table `product_variant_attribute_values` requires attribute_id.
     *
     * @param  array $attributeValueIds
     * @return array  [ attribute_value_id => ['attribute_id' => X], ... ]
     */
    private function buildAttributeValueSyncData(array $attributeValueIds): array
    {
        if (empty($attributeValueIds)) {
            return [];
        }

        $values = AttributeValue::whereIn('id', $attributeValueIds)->get(['id', 'attribute_id']);

        $syncData = [];
        foreach ($values as $value) {
            $syncData[$value->id] = ['attribute_id' => $value->attribute_id];
        }

        return $syncData;
    }
}

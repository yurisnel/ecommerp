<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Attribute Service
 * Manages product attributes and their values
 */
class AttributeService
{
    /**
     * Get all attributes with pagination
     */
    public function getAll(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return Attribute::with($relations)
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Get all attributes (no pagination)
     */
    public function getAllAttributes(array $relations = []): \Illuminate\Database\Eloquent\Collection
    {
        return Attribute::with($relations)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get attribute by ID
     */
    public function getById(int $id, array $relations = []): ?Attribute
    {
        return Attribute::with($relations)->find($id);
    }

    /**
     * Get attribute by code
     */
    public function getByCode(string $code): ?Attribute
    {
        return Attribute::where('code', $code)->first();
    }

    /**
     * Create a new attribute
     */
    public function create(array $data): Attribute
    {
        // Generate code from name if not provided
        if (!isset($data['code'])) {
            $data['code'] = \Illuminate\Support\Str::slug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            $attribute = Attribute::create($data);

            // Create attribute values if provided
            if (isset($data['values']) && is_array($data['values'])) {
                foreach ($data['values'] as $index => $valueData) {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                        'value_es' => $valueData['value_es'] ?? null,
                        'color_code' => $valueData['color_code'] ?? null,
                        'image' => $valueData['image'] ?? null,
                        'sort_order' => $valueData['sort_order'] ?? $index,
                    ]);
                }
            }

            return $attribute->fresh(['values']);
        });
    }

    /**
     * Update an attribute
     */
    public function update(int $id, array $data): Attribute
    {
        return DB::transaction(function () use ($id, $data) {
            $attribute = Attribute::findOrFail($id);
            $attribute->update($data);

            // Update values if provided
            if (isset($data['values']) && is_array($data['values'])) {
                $existingValues = $attribute->values()->get()->keyBy('value');
                $incomingValues = collect($data['values'])->pluck('value')->toArray();

                foreach ($data['values'] as $index => $valueData) {
                    if ($existingValues->has($valueData['value'])) {
                        // Update existing
                        $existingValues[$valueData['value']]->update([
                            'value_es' => $valueData['value_es'] ?? null,
                            'color_code' => $valueData['color_code'] ?? null,
                            'image' => $valueData['image'] ?? null,
                            'sort_order' => $valueData['sort_order'] ?? $index,
                        ]);
                    } else {
                        // Create new
                        $attribute->values()->create([
                            'value' => $valueData['value'],
                            'value_es' => $valueData['value_es'] ?? null,
                            'color_code' => $valueData['color_code'] ?? null,
                            'image' => $valueData['image'] ?? null,
                            'sort_order' => $valueData['sort_order'] ?? $index,
                        ]);
                    }
                }

                // Delete values not in the incoming list
                $attribute->values()->whereNotIn('value', $incomingValues)->delete();
            }

            return $attribute->fresh(['values']);
        });
    }

    /**
     * Delete an attribute
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $attribute = Attribute::findOrFail($id);
            
            // Delete associated values first
            $attribute->values()->delete();
            
            // Delete the attribute
            $attribute->delete();
        });
    }

    /**
     * Get filterable attributes
     */
    public function getFilterableAttributes(): \Illuminate\Database\Eloquent\Collection
    {
        return Attribute::filterable()
            ->with('values')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Search attributes
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Attribute::query();

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_filterable'])) {
            $query->where('is_filterable', $filters['is_filterable']);
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }

    // ===== ATTRIBUTE VALUES =====

    /**
     * Get attribute value by ID
     */
    public function getValueById(int $id): ?AttributeValue
    {
        return AttributeValue::with('attribute')->find($id);
    }

    /**
     * Create attribute value
     */
    public function createValue(int $attributeId, array $data): AttributeValue
    {
        $attribute = Attribute::findOrFail($attributeId);
        return $attribute->values()->create($data);
    }

    /**
     * Update attribute value
     */
    public function updateValue(int $id, array $data): AttributeValue
    {
        $value = AttributeValue::findOrFail($id);
        $value->update($data);
        return $value->fresh();
    }

    /**
     * Delete attribute value
     */
    public function deleteValue(int $id): void
    {
        AttributeValue::findOrFail($id)->delete();
    }

    /**
     * Get values for an attribute
     */
    public function getValuesByAttribute(int $attributeId): \Illuminate\Database\Eloquent\Collection
    {
        return AttributeValue::where('attribute_id', $attributeId)
            ->orderBy('sort_order')
            ->get();
    }
}

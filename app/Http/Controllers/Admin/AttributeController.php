<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttributeController extends Controller
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * List all attributes
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $filters = $request->only(['search', 'type', 'is_filterable']);

        $attributes = $this->attributeService->search($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Attributes retrieved successfully',
            'data' => $attributes
        ]);
    }

    /**
     * Get all attributes (no pagination)
     */
    public function all(): JsonResponse
    {
        $attributes = $this->attributeService->getAllAttributes(['values']);

        return response()->json([
            'success' => true,
            'message' => 'Attributes retrieved successfully',
            'data' => $attributes
        ]);
    }

    /**
     * Get filterable attributes
     */
    public function filterable(): JsonResponse
    {
        $attributes = $this->attributeService->getFilterableAttributes();

        return response()->json([
            'success' => true,
            'message' => 'Filterable attributes retrieved successfully',
            'data' => $attributes
        ]);
    }

    /**
     * Show a single attribute
     */
    public function show(int $id): JsonResponse
    {
        $attribute = $this->attributeService->getById($id, ['values']);

        if (!$attribute) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attribute retrieved successfully',
            'data' => $attribute
        ]);
    }

    /**
     * Create a new attribute
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|unique:attributes,code',
            'description' => 'nullable|string',
            'type' => 'nullable|in:select,radio,checkbox,text',
            'is_required' => 'nullable|boolean',
            'is_filterable' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:100',
            'values.*.value_es' => 'nullable|string|max:100',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.image' => 'nullable|string',
        ]);

        $attribute = $this->attributeService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute created successfully',
            'data' => $attribute
        ], 201);
    }

    /**
     * Update an attribute
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'code' => 'sometimes|string|max:50|unique:attributes,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'nullable|in:select,radio,checkbox,text',
            'is_required' => 'nullable|boolean',
            'is_filterable' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:100',
            'values.*.value_es' => 'nullable|string|max:100',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.image' => 'nullable|string',
        ]);

        $attribute = $this->attributeService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated successfully',
            'data' => $attribute
        ]);
    }

    /**
     * Delete an attribute
     */
    public function destroy(int $id): JsonResponse
    {
        $this->attributeService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted successfully'
        ]);
    }

    // ===== ATTRIBUTE VALUES =====

    /**
     * Get values for an attribute
     */
    public function values(int $attributeId): JsonResponse
    {
        $values = $this->attributeService->getValuesByAttribute($attributeId);

        return response()->json([
            'success' => true,
            'message' => 'Attribute values retrieved successfully',
            'data' => $values
        ]);
    }

    /**
     * Create attribute value
     */
    public function storeValue(Request $request, int $attributeId): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string|max:100',
            'value_es' => 'nullable|string|max:100',
            'color_code' => 'nullable|string|max:7',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $value = $this->attributeService->createValue($attributeId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value created successfully',
            'data' => $value
        ], 201);
    }

    /**
     * Update attribute value
     */
    public function updateValue(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'sometimes|string|max:100',
            'value_es' => 'nullable|string|max:100',
            'color_code' => 'nullable|string|max:7',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $value = $this->attributeService->updateValue($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value updated successfully',
            'data' => $value
        ]);
    }

    /**
     * Delete attribute value
     */
    public function destroyValue(int $id): JsonResponse
    {
        $this->attributeService->deleteValue($id);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value deleted successfully'
        ]);
    }
}

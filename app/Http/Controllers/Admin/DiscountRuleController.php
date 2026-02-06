<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\DiscountRuleService;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscountRuleController extends BaseController
{
    protected $calculationService;

    public function __construct(DiscountRuleService $service, DiscountService $calculationService)
    {
        parent::__construct($service);
        $this->calculationService = $calculationService;
    }

    /**
     * Display a listing of discount rules with relations
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['search', 'status', 'customer_group_id']);
        $relations = ['customerGroup', 'products', 'categories'];

        if ($perPage == -1) {
            $data = $this->service->getAll($relations);
        } else {
            $data = $this->service->search($filters, $perPage, $relations);
        }

        return $this->successResponse($data, 'Discount rules retrieved successfully');
    }

    /**
     * Store discount rule with relations
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $rule = $this->service->create($validated);

        // Attach products if specified
        if (!empty($validated['product_ids'])) {
            $rule->products()->attach($validated['product_ids']);
        }

        // Attach categories if specified
        if (!empty($validated['category_ids'])) {
            $rule->categories()->attach($validated['category_ids']);
        }

        return $this->successResponse($rule->load(['products', 'categories']), 'Discount rule created successfully', 201);
    }

    /**
     * Update discount rule with relations
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRequest($request, $id);
        $this->service->update($id, $validated);
        $rule = $this->service->getById($id);

        // Sync products if specified
        if (isset($validated['product_ids'])) {
            $rule->products()->sync($validated['product_ids']);
        }

        // Sync categories if specified
        if (isset($validated['category_ids'])) {
            $rule->categories()->sync($validated['category_ids']);
        }

        return $this->successResponse($rule->load(['products', 'categories']), 'Discount rule updated successfully');
    }

    /**
     * Calculate applicable discounts
     */
    public function calculateDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'category_id' => 'nullable|exists:categories,id',
            'quantity' => 'required|numeric|min:0.01',
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->calculationService->calculateBestDiscount(
            $validated['customer_id'] ?? null,
            $validated['product_id'],
            $validated['category_id'] ?? null,
            $validated['quantity'],
            $validated['amount']
        );

        return $this->successResponse($result, 'Best discount calculated');
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:discount_rules,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'applies_to' => 'required|in:all,products,categories',
            'min_quantity' => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:0',
            'combinable' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);
    }
}

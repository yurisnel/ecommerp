<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\ShippingMethodService;
use App\Services\ShippingService;
use App\Models\CustomerAddress;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends BaseController
{
    protected $calculationService;

    public function __construct(ShippingMethodService $service, ShippingService $calculationService)
    {
        parent::__construct($service);
        $this->calculationService = $calculationService;
    }

    /**
     * Get available shipping methods for address
     */
    public function getAvailableMethods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:customer_addresses,id',
            'order_amount' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'item_count' => 'nullable|integer|min:0',
        ]);

        $address = CustomerAddress::findOrFail($validated['address_id']);

        $methods = $this->calculationService->getAvailableShippingMethods(
            $address,
            $validated['order_amount'] ?? 0,
            $validated['weight'] ?? 0,
            $validated['item_count'] ?? 0
        );

        return $this->successResponse($methods, 'Available shipping methods retrieved');
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'address_id' => 'required|exists:customer_addresses,id',
            'order_amount' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'item_count' => 'nullable|integer|min:0',
        ]);

        $address = CustomerAddress::findOrFail($validated['address_id']);

        $cost = $this->calculationService->calculateShippingCost(
            $validated['shipping_method_id'],
            $address,
            $validated['order_amount'] ?? 0,
            $validated['weight'] ?? 0,
            $validated['item_count'] ?? 0
        );

        return $this->successResponse(['cost' => $cost], 'Shipping cost calculated');
    }

    /**
     * List shipping methods (Override index if needed, but BaseController handles it)
     */
    public function listMethods(): JsonResponse
    {
        return $this->index(request());
    }

    /**
     * List shipping zones
     */
    public function listZones(): JsonResponse
    {
        $zones = ShippingZone::where('status', 'active')
            ->with('shippingRates.shippingMethod')
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse($zones, 'Shipping zones retrieved');
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:shipping_methods,code,' . $id,
            'type' => 'required|in:flat_rate,pickup,carrier',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);
    }
}

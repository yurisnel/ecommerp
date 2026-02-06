<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\PaymentMethodService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends BaseController
{
    /** @var \App\Services\PaymentMethodService */
    protected $service;

    public function __construct(PaymentMethodService $service)
    {
        parent::__construct($service);
    }

    /**
     * Get active payment methods
     */
    public function active(): JsonResponse
    {
        $methods = $this->service->getActive();
        return $this->successResponse($methods, 'Active payment methods retrieved');
    }

    /**
     * Update payment method configuration
     */
    public function updateConfig(Request $request, int $id): JsonResponse
    {
        $method = $this->service->getById($id);
        if (!$method) {
            return $this->errorResponse('Payment method not found', 404);
        }

        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.key' => 'required|string',
            'configs.*.value' => 'required',
            'configs.*.encrypt' => 'nullable|boolean',
        ]);

        foreach ($validated['configs'] as $config) {
            $method->setConfig(
                $config['key'],
                $config['value'],
                $config['encrypt'] ?? false
            );
        }

        return $this->successResponse($method->load('configs'), 'Payment method configuration updated');
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $method = $this->service->getById($id);
        if (!$method) {
            return $this->errorResponse('Payment method not found', 404);
        }

        $method->status = $method->status === 'active' ? 'inactive' : 'active';
        $method->save();

        return $this->successResponse($method, 'Payment method status updated');
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_methods,code,' . $id,
            'type' => 'required|string|max:50',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);
    }
}

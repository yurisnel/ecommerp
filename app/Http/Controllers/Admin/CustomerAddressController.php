<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\CustomerAddressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerAddressController extends BaseController
{
    /** @var \App\Services\CustomerAddressService */
    protected $service;

    public function __construct(CustomerAddressService $service)
    {
        parent::__construct($service);
    }

    /**
     * List addresses for a customer
     */
    public function indexByCustomer(int $customerId): JsonResponse
    {
        $addresses = $this->service->getByCustomer($customerId);
        return $this->successResponse($addresses, 'Customer addresses retrieved');
    }

    /**
     * Store new address
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $address = $this->service->create($validated);

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return $this->successResponse($address->fresh(), 'Address created successfully', 201);
    }

    /**
     * Update address
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRequest($request, $id);
        $this->service->update($id, $validated);
        $address = $this->service->getById($id);

        if ($request->boolean('is_default')) {
            $address->setAsDefault();
        }

        return $this->successResponse($address->fresh(), 'Address updated successfully');
    }

    /**
     * Set address as default
     */
    public function setDefault(int $id): JsonResponse
    {
        $address = $this->service->getById($id);
        if (!$address) {
            return $this->errorResponse('Address not found', 404);
        }

        $address->setAsDefault();
        return $this->successResponse($address->fresh(), 'Address set as default');
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'customer_id' => 'required_without:id|exists:customers,id',
            'label' => 'nullable|string|max:100',
            'contact_name' => 'required|string|max:200',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'type' => 'required|in:shipping,billing,both',
            'is_default' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);
    }
}

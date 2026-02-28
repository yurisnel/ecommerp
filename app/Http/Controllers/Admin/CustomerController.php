<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\CustomerService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends BaseController
{
    public function __construct(CustomerService $service)
    {
        parent::__construct($service);
    }

    /**
     * Get customer statistics
     */
    public function stats(): JsonResponse
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
            ]
        ]);
    }

    /**
     * Validate customer request
     */
    protected function validateRequest(Request $request, ?int $id = null): array
    {
        $rules = [
            'customer_number' => 'nullable|string|max:50|unique:customers,customer_number,' . $id,
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',            
            'notes' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];

        return $request->validate($rules);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\BaseService;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends BaseController
{
    public function __construct(SupplierService $service)
    {
        parent::__construct($service);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'company_name' => 'nullable|string|max:200',
            'tax_id' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);
    }
}

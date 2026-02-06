<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\CustomerGroupService;
use Illuminate\Http\Request;

class CustomerGroupController extends BaseController
{
    public function __construct(CustomerGroupService $service)
    {
        parent::__construct($service);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:customer_groups,code,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:customer_groups,id',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'priority' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);
    }
}

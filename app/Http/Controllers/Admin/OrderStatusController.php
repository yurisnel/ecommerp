<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;

class OrderStatusController extends BaseController
{
    public function __construct(OrderStatusService $service)
    {
        parent::__construct($service);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:order_statuses,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);
    }
}

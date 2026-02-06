<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\SalesChannelService;
use Illuminate\Http\Request;

class SalesChannelController extends BaseController
{
    public function __construct(SalesChannelService $service)
    {
        parent::__construct($service);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:sales_channels,code,' . $id,
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);
    }
}

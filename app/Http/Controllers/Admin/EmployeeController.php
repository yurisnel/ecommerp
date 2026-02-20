<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{
    public function __construct(EmployeeService $service)
    {
        parent::__construct($service);
    }

    protected function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number,' . $id,
            'user_id' => 'required|exists:users,id|unique:employees,user_id,' . $id,
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'required|string|max:100',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after:hire_date',
            'salary' => 'nullable|numeric|min:0',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,on_leave,terminated',
        ]);
    }
}

<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository extends BaseRepository
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    public function searchEmployees(array $filters, int $perPage = 15)
    {
        $query = $this->model->with(['user', 'department']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('employee_number', 'like', "%{$filters['search']}%")
                    ->orWhere('position', 'like', "%{$filters['search']}%")
                    ->orWhereHas('user', function ($userQuery) use ($filters) {
                        $userQuery->where('name', 'like', "%{$filters['search']}%");
                    });
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }
}

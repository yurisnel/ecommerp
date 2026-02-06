<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;

class EmployeeService extends BaseService
{
    public function __construct(EmployeeRepository $repository)
    {
        parent::__construct($repository);
    }

    protected function validateData(array $data, ?int $id = null): array
    {
        if (!isset($data['employee_number'])) {
            $data['employee_number'] = $this->generateEmployeeNumber();
        }

        return $data;
    }

    public function searchEmployees(array $filters, int $perPage = 15)
    {
        return $this->repository->searchEmployees($filters, $perPage);
    }

    private function generateEmployeeNumber(): string
    {
        $prefix = 'EMP';
        $date = date('Ymd');
        $lastEmployee = \App\Models\Employee::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEmployee ? (int)substr($lastEmployee->employee_number, -4) + 1 : 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Services;

use App\Repositories\CustomerRepository;

class CustomerService extends BaseService
{
    public function __construct(CustomerRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Validate customer data
     */
    protected function validateData(array $data, ?int $id = null): array
    {
        // Generate customer number if not provided
        if (!isset($data['customer_number'])) {
            $data['customer_number'] = $this->generateCustomerNumber();
        }

        return $data;
    }

    /**
     * Search customers
     */
    public function searchCustomers(array $filters, int $perPage = 15)
    {
        return $this->repository->searchCustomers($filters, $perPage);
    }

    /**
     * Generate unique customer number
     */
    private function generateCustomerNumber(): string
    {
        $prefix = 'CUST';
        $date = date('Ymd');
        $lastCustomer = \App\Models\Customer::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastCustomer ? (int)substr($lastCustomer->customer_number, -4) + 1 : 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

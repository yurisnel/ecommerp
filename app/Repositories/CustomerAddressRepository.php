<?php

namespace App\Repositories;

use App\Models\CustomerAddress;

class CustomerAddressRepository extends BaseRepository
{
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

    public function findByCustomer(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)
            ->orderBy('is_default', 'desc')
            ->get();
    }
}

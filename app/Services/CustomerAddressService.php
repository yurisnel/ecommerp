<?php

namespace App\Services;

use App\Repositories\CustomerAddressRepository;

class CustomerAddressService extends BaseService
{
    /** @var \App\Repositories\CustomerAddressRepository */
    protected $repository;

    public function __construct(CustomerAddressRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getByCustomer(int $customerId)
    {
        return $this->repository->findByCustomer($customerId);
    }
}

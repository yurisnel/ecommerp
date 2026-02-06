<?php

namespace App\Services;

use App\Repositories\SupplierRepository;

class SupplierService extends BaseService
{
    /**
     * @var SupplierRepository
     */
    protected $repository;

    public function __construct(SupplierRepository $repository)
    {
        parent::__construct($repository);
    }
}

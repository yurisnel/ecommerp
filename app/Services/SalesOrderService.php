<?php

namespace App\Services;

use App\Repositories\SalesOrderRepository;

class SalesOrderService extends BaseService
{
    public function __construct(SalesOrderRepository $repository)
    {
        parent::__construct($repository);
    }
}

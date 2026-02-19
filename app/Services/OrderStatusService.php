<?php

namespace App\Services;

use App\Repositories\OrderStatusRepository;

class OrderStatusService extends BaseService
{
    public function __construct(OrderStatusRepository $repository)
    {
        parent::__construct($repository);
    }
}

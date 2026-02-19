<?php

namespace App\Repositories;

use App\Models\OrderStatus;

class OrderStatusRepository extends BaseRepository
{
    public function __construct(OrderStatus $model)
    {
        parent::__construct($model);
    }
}

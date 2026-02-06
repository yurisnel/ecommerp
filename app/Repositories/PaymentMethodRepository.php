<?php

namespace App\Repositories;

use App\Models\PaymentMethod;

class PaymentMethodRepository extends BaseRepository
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function findAllActive(array $columns = ['*'])
    {
        return $this->model->where('status', 'active')
            ->orderBy('sort_order')
            ->get($columns);
    }
}

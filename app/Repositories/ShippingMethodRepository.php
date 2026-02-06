<?php

namespace App\Repositories;

use App\Models\ShippingMethod;

class ShippingMethodRepository extends BaseRepository
{
    public function __construct(ShippingMethod $model)
    {
        parent::__construct($model);
    }
}

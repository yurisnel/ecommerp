<?php

namespace App\Repositories;

use App\Models\CustomerGroup;

class CustomerGroupRepository extends BaseRepository
{
    public function __construct(CustomerGroup $model)
    {
        parent::__construct($model);
    }
}

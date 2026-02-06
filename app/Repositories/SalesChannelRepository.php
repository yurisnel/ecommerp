<?php

namespace App\Repositories;

use App\Models\SalesChannel;

class SalesChannelRepository extends BaseRepository
{
    public function __construct(SalesChannel $model)
    {
        parent::__construct($model);
    }
}

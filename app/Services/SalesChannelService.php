<?php

namespace App\Services;

use App\Repositories\SalesChannelRepository;

class SalesChannelService extends BaseService
{
    public function __construct(SalesChannelRepository $repository)
    {
        parent::__construct($repository);
    }
}

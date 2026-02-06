<?php

namespace App\Services;

use App\Repositories\DiscountRuleRepository;

class DiscountRuleService extends BaseService
{
    public function __construct(DiscountRuleRepository $repository)
    {
        parent::__construct($repository);
    }
}

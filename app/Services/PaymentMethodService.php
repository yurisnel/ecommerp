<?php

namespace App\Services;

use App\Repositories\PaymentMethodRepository;

class PaymentMethodService extends BaseService
{
    /** @var \App\Repositories\PaymentMethodRepository */
    protected $repository;

    public function __construct(PaymentMethodRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getActive()
    {
        return $this->repository->findAllActive();
    }
}

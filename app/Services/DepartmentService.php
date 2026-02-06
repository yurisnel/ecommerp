<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;

class DepartmentService extends BaseService
{
    /**
     * @var DepartmentRepository
     */
    protected $repository;

    public function __construct(DepartmentRepository $repository)
    {
        parent::__construct($repository);
    }
}

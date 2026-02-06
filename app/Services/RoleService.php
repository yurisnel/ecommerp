<?php

namespace App\Services;

use App\Repositories\RoleRepository;

class RoleService extends BaseService
{
    /**
     * @var RoleRepository
     */
    protected $repository;

    public function __construct(RoleRepository $repository)
    {
        parent::__construct($repository);
    }
}

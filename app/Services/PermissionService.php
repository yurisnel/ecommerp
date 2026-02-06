<?php

namespace App\Services;

use App\Repositories\PermissionRepository;

class PermissionService extends BaseService
{
    /**
     * @var PermissionRepository
     */
    protected $repository;

    public function __construct(PermissionRepository $repository)
    {
        parent::__construct($repository);
    }
}

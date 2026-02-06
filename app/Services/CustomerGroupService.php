<?php

namespace App\Services;

use App\Repositories\CustomerGroupRepository;

class CustomerGroupService extends BaseService
{
    public function __construct(CustomerGroupRepository $repository)
    {
        parent::__construct($repository);
    }

    protected function validateData(array $data, ?int $id = null): array
    {
        if (!isset($data['code']) && isset($data['name'])) {
            $data['code'] = strtoupper(\Illuminate\Support\Str::slug($data['name'], '_'));
        }

        return $data;
    }
}

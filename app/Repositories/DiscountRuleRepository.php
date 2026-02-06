<?php

namespace App\Repositories;

use App\Models\DiscountRule;

class DiscountRuleRepository extends BaseRepository
{
    public function __construct(DiscountRule $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        return $query->orderBy('priority', 'desc')->paginate($perPage, $columns);
    }
}

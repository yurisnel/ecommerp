<?php

namespace App\Repositories;

use App\Models\SalesOrder;

class SalesOrderRepository extends BaseRepository
{
    public function __construct(SalesOrder $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage, $columns);
    }
}

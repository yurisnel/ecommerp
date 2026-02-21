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

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('order_status_id', $filters['status']);
        }

        if (isset($filters['customer_id']) && !empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['warehouse_id']) && !empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['sales_channel_id']) && !empty($filters['sales_channel_id'])) {
            $query->where('sales_channel_id', $filters['sales_channel_id']);
        }

        if (isset($filters['date_start']) && !empty($filters['date_start'])) {
            $query->whereDate('order_date', '>=', $filters['date_start']);
        }

        if (isset($filters['date_end']) && !empty($filters['date_end'])) {
            $query->whereDate('order_date', '<=', $filters['date_end']);
        }

        // Filter by payment method (through payments relation)
        if (isset($filters['payment_method_id']) && !empty($filters['payment_method_id'])) {
            $query->whereHas('payments', function ($pq) use ($filters) {
                $pq->where('payment_method_id', $filters['payment_method_id']);
            });
        }

        return $query->latest()->paginate($perPage, $columns);
    }
}

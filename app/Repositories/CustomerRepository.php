<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * Override search to include relationships
     */
    public function search(array $filters, int $perPage = 15, array $columns = ['*'], array $relations = ['customerGroup']): LengthAwarePaginator
    {
        // Always include customerGroup relationship
        $relations = array_merge($relations, ['customerGroup']);
        $relations = array_unique($relations);
        
        $query = $this->model->with($relations);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('customer_number', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage, $columns);
    }

    /**
     * Override paginate to include relationships by default
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['customerGroup']): LengthAwarePaginator
    {
        // Always include customerGroup relationship
        $relations = array_merge($relations, ['customerGroup']);
        $relations = array_unique($relations);
        
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * Search customers with filters
     */
    public function searchCustomers(array $filters, int $perPage = 15)
    {
        return $this->search($filters, $perPage);
    }
}

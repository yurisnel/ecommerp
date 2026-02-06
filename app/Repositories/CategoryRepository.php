<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $relations = array_unique(array_merge($relations, ['parent']));
        return parent::paginate($perPage, $columns, $relations);
    }

    public function search(array $filters, int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $relations = array_unique(array_merge($relations, ['parent']));
        return parent::search($filters, $perPage, $columns, $relations);
    }
}

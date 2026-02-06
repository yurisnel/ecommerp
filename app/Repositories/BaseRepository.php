<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository
 * Provides common CRUD operations for all repositories
 */
abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     * 
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function findAll(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * Find record by ID
     * 
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Find record by specific field
     * 
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function findBy(string $field, $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($field, $value)->first($columns);
    }

    /**
     * Find multiple records by field
     * 
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function findAllBy(string $field, $value, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->where($field, $value)->get($columns);
    }

    /**
     * Create new record
     * 
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update existing record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->findById($id);
        
        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Paginate records
     * 
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * Search records with filters
     * 
     * @param array $filters
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function search(array $filters, int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, 'like', "%{$value}%");
                }
            }
        }

        return $query->paginate($perPage, $columns);
    }

    /**
     * Count records
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if record exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }
}

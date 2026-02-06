<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Base Service
 * Provides common business logic for all services
 */
abstract class BaseService
{
    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * BaseService constructor.
     * @param BaseRepository $repository
     */
    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all records
     * 
     * @param array $relations
     * @return Collection
     */
    public function getAll(array $relations = []): Collection
    {
        try {
            return $this->repository->findAll(['*'], $relations);
        } catch (Exception $e) {
            Log::error('Error getting all records: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get record by ID
     * 
     * @param int $id
     * @param array $relations
     * @return Model|null
     */
    public function getById(int $id, array $relations = []): ?Model
    {
        try {
            return $this->repository->findById($id, ['*'], $relations);
        } catch (Exception $e) {
            Log::error("Error getting record by ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new record
     * 
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public function create(array $data): Model
    {
        DB::beginTransaction();
        
        try {
            $validated = $this->validateData($data);
            $record = $this->repository->create($validated);
            
            DB::commit();
            
            return $record;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating record: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update existing record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function update(int $id, array $data): bool
    {
        DB::beginTransaction();
        
        try {
            $validated = $this->validateData($data, $id);
            $result = $this->repository->update($id, $validated);
            
            if (!$result) {
                throw new Exception("Record with ID {$id} not found");
            }
            
            DB::commit();
            
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating record {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            $result = $this->repository->delete($id);
            
            if (!$result) {
                throw new Exception("Record with ID {$id} not found");
            }
            
            DB::commit();
            
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error deleting record {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Paginate records
     * 
     * @param int $perPage
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        try {
            return $this->repository->paginate($perPage, ['*'], $relations);
        } catch (Exception $e) {
            Log::error('Error paginating records: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search records with filters
     * 
     * @param array $filters
     * @param int $perPage
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function search(array $filters, int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        try {
            return $this->repository->search($filters, $perPage, ['*'], $relations);
        } catch (Exception $e) {
            Log::error('Error searching records: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate data before create/update
     * Override this method in child classes for specific validation
     * 
     * @param array $data
     * @param int|null $id
     * @return array
     */
    protected function validateData(array $data, ?int $id = null): array
    {
        // Override in child classes for specific validation
        return $data;
    }

    /**
     * Get count of records
     * 
     * @return int
     */
    public function count(): int
    {
        try {
            return $this->repository->count();
        } catch (Exception $e) {
            Log::error('Error counting records: ' . $e->getMessage());
            throw $e;
        }
    }
}

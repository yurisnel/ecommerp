<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService extends BaseService
{
    /**
     * @var CategoryRepository
     */
    protected $repository;

    public function __construct(CategoryRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Validate category data
     */
    protected function validateData(array $data, ?int $id = null): array
    {
        // Simple slug generation if missing
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }
        return $data;
    }
}

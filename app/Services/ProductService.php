<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use App\Models\ProductImage;

class ProductService extends BaseService
{
    /**
     * @var ProductRepository
     */
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Validate product data
     */
    protected function validateData(array $data, ?int $id = null): array
    {
        // Generate slug if not provided
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        return $data;
    }

    /**
     * Search products (Override BaseService search)
     */
    public function search(array $filters, int $perPage = 15, array $relations = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->repository->searchProducts($filters, $perPage);
    }

    /**
     * Create product with categories
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $product = parent::create($data);

            if (isset($data['categories']) && is_array($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            if (isset($data['product_images']) && is_array($data['product_images'])) {
                foreach ($data['product_images'] as $index => $imgData) {
                    $product->images()->create([
                        'url' => $imgData['url'],
                        'is_default' => $imgData['is_default'] ?? false,
                        'sort_order' => $index
                    ]);
                }
            }

            return $product;
        });
    }

    /**
     * Update product with categories
     */
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $result = parent::update($id, $data);
            $product = $this->getById($id);

            if ($product) {
                if (isset($data['categories']) && is_array($data['categories'])) {
                    $product->categories()->sync($data['categories']);
                }

                if (isset($data['product_images']) && is_array($data['product_images'])) {
                    // Simple approach: delete all and re-create to maintain order and sync
                    $product->images()->delete();
                    foreach ($data['product_images'] as $index => $imgData) {
                        $product->images()->create([
                            'url' => $imgData['url'],
                            'is_default' => $imgData['is_default'] ?? false,
                            'sort_order' => $index
                        ]);
                    }
                }
            }

            return $result;
        });
    }
}

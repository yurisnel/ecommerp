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

                $this->syncImages($product, $data);
            }

            return $result;
        });
    }

    public function syncImages($product, $data): void
    {
        if (!empty($data['product_images']) && is_array($data['product_images'])) {

            // Imágenes actuales del producto indexadas por URL
            $existingImages = $product->images()->get()->keyBy('url');

            foreach ($data['product_images'] as $index => $imgData) {

                $url = $imgData['url'];

                if ($existingImages->has($url)) {
                    // Ya existe → actualizar datos si hace falta
                    $existingImages[$url]->update([
                        'is_default' => $imgData['is_default'] ?? false,
                        'sort_order' => $index
                    ]);
                } else {
                    // No existe → crear nueva
                    $product->images()->create([
                        'url' => $url,
                        'is_default' => $imgData['is_default'] ?? false,
                        'sort_order' => $index
                    ]);
                }
            }

            //Eliminar SOLO las imágenes que ya no vienen en la API
            $incomingUrls = collect($data['product_images'])->pluck('url')->toArray();
            $product->images()->whereNotIn('url', $incomingUrls)->delete();
        }
        //asegurar solo una imagen por defecto
        /*$product->images()
                ->where('is_default', true)
                ->orderBy('sort_order')
                ->skip(1)
                ->update(['is_default' => false]);*/
    }
}

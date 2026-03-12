<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    /**
     * Show product with relations
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $product = $this->service->getById($id, ['categories', 'images', 'variants']);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => __('api.product_not_found')
            ], 404);
        }

        return $this->successResponse($product, 'Product retrieved successfully');
    }

    /**
     * Validate product request
     */
    protected function validateRequest(Request $request, ?int $id = null): array
    {
        $rules = [
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|unique:products,slug,' . $id,
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $id,
            'unit' => 'required|string|in:pcs,kg,m,l,box',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,discontinued',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'product_images' => 'nullable|array',
            'product_images.*.url' => 'required|string',
            'product_images.*.is_default' => 'nullable|boolean',
        ];

        return $request->validate($rules);
    }
}

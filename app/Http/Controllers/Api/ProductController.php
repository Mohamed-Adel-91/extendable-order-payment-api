<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 6);
        $perPage = max(1, min($perPage, 100));

        $paginator = $this->productService->paginate($perPage);

        return $this->successWithPagination(
            data: ProductResource::collection($paginator->items()),
            paginator: $paginator,
            message: trans('api.response.success')
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->createdResponse(
            data: (new ProductResource($product))->resolve(),
            message: 'Product created successfully.'
        );
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(
            data: (new ProductResource($product))->resolve(),
            message: 'Product fetched successfully.'
        );
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return $this->successResponse(
            data: (new ProductResource($product))->resolve(),
            message: 'Product updated successfully.'
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->successResponse(
            data: [],
            message: 'Product deleted successfully.'
        );
    }
}

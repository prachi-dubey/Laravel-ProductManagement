<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\IndexProductRequest;
use App\Http\Requests\Api\Product\SaveProductRequest;
use App\Http\Resources\Api\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /** @var ProductService */
    private $products;

    public function __construct(ProductService $products)
    {
        $this->products = $products;
    }

    public function index(IndexProductRequest $request): JsonResponse
    {
        $paginator = $this->products->index($request->validated());

        return $this->paginated(
            ProductResource::collection($paginator),
            __('messages.products.listed')
        );
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->products->show($product);
        return $this->success(
            __('messages.products.shown'),
            new ProductResource($product)
        );
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create($request->validated());
        return $this->success(
            __('messages.products.created'),
            new ProductResource($product),
            Response::HTTP_CREATED
        );
    }

    public function update(SaveProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->products->update($product, $request->validated());

        return $this->success(
            __('messages.products.updated'),
            new ProductResource($product)
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return $this->success(__('messages.products.deleted'));
    }

    public function syncCategories(SaveProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->products->syncCategories($product, $request->validated()['category_ids']);

        return $this->success(
            __('messages.products.categories_synced'),
            new ProductResource($product)
        );
    }
}

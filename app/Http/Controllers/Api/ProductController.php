<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexProductRequest;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Requests\Api\UploadProductImageRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Support\ApiListQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin HTTP layer — ProductService → ProductRepository → Eloquent.
 */
class ProductController extends Controller
{
    /** @var ProductService */
    private $products;

    public function __construct(ProductService $products)
    {
        $this->products = $products;
    }

    /**
     * GET /api/products
     *
     * Query: page, per_page, sort, search, category_id, tag_id, is_active, min_price, max_price
     */
    public function index(IndexProductRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $perPage = ApiListQuery::perPage($request)['per_page'];
        $paginator = $this->products->paginate($request->validated(), $perPage);
        $paginator->appends($request->query());

        return response()->json(
            ApiListQuery::paginatedResponse(
                ProductResource::collection($paginator),
                'Products retrieved successfully.'
            )
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->products->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product->load(['category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->products->update($product, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data' => null,
        ]);
    }

    public function uploadImage(UploadProductImageRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->products->uploadImage($product, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Product image uploaded successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function deleteImage(Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->products->deleteImage($product);

        return response()->json([
            'success' => true,
            'message' => 'Product image deleted successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function syncTags(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        $product = $this->products->syncTags($product, $validated['tag_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Product tags synced successfully.',
            'data' => new ProductResource($product),
        ]);
    }
}

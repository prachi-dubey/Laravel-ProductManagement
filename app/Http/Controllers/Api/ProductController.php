<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Eager-loads category (1:N inverse) and tags (M:N).
     */
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'tags'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }

    /**
     * POST /api/products
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::query()->create($data);

        if ($tagIds !== []) {
            $product->tags()->sync($tagIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product->load(['category', 'tags']),
        ], 201);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $product,
        ]);
    }

    /**
     * PUT/PATCH /api/products/{product}
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['tag_ids']);

        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        if (is_array($tagIds)) {
            $product->tags()->sync($tagIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->fresh()->load(['category', 'tags']),
        ]);
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->orderItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product that appears on existing orders.',
            ], 422);
        }

        $product->tags()->detach();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
            'data' => null,
        ]);
    }

    /**
     * PUT /api/products/{product}/tags
     * Replace the product's tags (M:N sync).
     */
    public function syncTags(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'tag_ids' => ['required', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        $product->tags()->sync($validated['tag_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Product tags synced successfully.',
            'data' => $product->fresh()->load(['category', 'tags']),
        ]);
    }
}

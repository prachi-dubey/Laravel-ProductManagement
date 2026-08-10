<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\ApiListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Eloquent data access for products — swap later without touching services.
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCatalog(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'tags']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== null && $filters['category_id'] !== '') {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['tag_id']) && $filters['tag_id'] !== null && $filters['tag_id'] !== '') {
            $tagId = $filters['tag_id'];
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null && $filters['min_price'] !== '') {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null && $filters['max_price'] !== '') {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sortRequest = Request::create('/', 'GET', [
            'sort' => $filters['sort'] ?? null,
        ]);

        ApiListQuery::applySort(
            $query,
            $sortRequest,
            ['id', 'name', 'price', 'stock', 'created_at', 'updated_at'],
            '-created_at'
        );

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->tags()->detach();
        $product->delete();
    }

    /**
     * @param  list<int>  $tagIds
     */
    public function syncTags(Product $product, array $tagIds): void
    {
        $product->tags()->sync($tagIds);
    }

    public function hasOrderItems(Product $product): bool
    {
        return $product->orderItems()->exists();
    }

    public function findForUpdate(int $id): ?Product
    {
        return Product::query()->lockForUpdate()->find($id);
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Product $product, array $relations = ['category', 'tags']): Product
    {
        return $product->fresh()->load($relations);
    }
}

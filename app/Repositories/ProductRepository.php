<?php

namespace App\Repositories;

use App\Interfaces\Product\ProductRepositoryInterface;
use App\Models\Product;
use App\Helper\ApiListHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(array $filters): LengthAwarePaginator
    {
        $builder = Product::with(['categories']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $builder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== null && $filters['category_id'] !== '') {
            $categoryId = $filters['category_id'];
            $builder->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $builder->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null && $filters['min_price'] !== '') {
            $builder->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null && $filters['max_price'] !== '') {
            $builder->where('price', '<=', $filters['max_price']);
        }

        ApiListHelper::applySort(
            $builder,
            $filters['sort'] ?? '',
            $filters['sort_direction'] ?? '',
            ['id', 'name', 'price', 'stock', 'created_at', 'updated_at'],
        );

        $perPage = ApiListHelper::perPage($filters['per_page'] ?? null);

        return $builder->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::create($data);
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
        $product->categories()->detach();
        $product->delete();
    }

    /**
     * @param  list<int>  $categoryIds
     */
    public function syncCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    public function hasOrderItems(Product $product): bool
    {
        return $product->orderItems()->exists();
    }

    public function findForUpdate(int $id): ?Product
    {
        return Product::lockForUpdate()->find($id);
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Product $product, array $relations = ['categories']): Product
    {
        return $product->fresh()->load($relations);
    }
}

<?php

namespace App\Repositories;

use App\Interfaces\Category\CategoryRepositoryInterface;
use App\Models\Category;
use App\Helper\ApiListHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $builder = Category::withCount('products');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $builder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $builder->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortRequest = Request::create('/', 'GET', [
            'sort' => $filters['sort'] ?? null,
        ]);

        ApiListHelper::applySort(
            $builder,
            $sortRequest,
            ['id', 'name', 'created_at', 'updated_at'],
            '-created_at'
        );

        return $builder->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }

    /**
     * @param  list<string>  $relations
     * @param  list<string>  $withCount
     */
    public function loadRelations(Category $category, array $relations = [], array $withCount = ['products']): Category
    {
        if ($relations !== []) {
            $category->load($relations);
        }

        if ($withCount !== []) {
            $category->loadCount($withCount);
        }

        return $category;
    }
}

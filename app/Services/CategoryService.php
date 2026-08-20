<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Interfaces\Category\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->categories->paginate($filters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->categories->loadRelations(
            $this->categories->create($data),
            [],
            ['products']
        );
    }

    public function show(Category $category): Category
    {
        return $this->categories->loadRelations(
            $category,
            ['products.categories'],
            ['products']
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->categories->update($category, $data);

        return $this->categories->loadRelations($category, [], ['products']);
    }

    public function delete(Category $category): void
    {
        if ($this->categories->hasProducts($category)) {
            throw ApiException::categoryInUse();
        }

        $this->categories->delete($category);
    }
}

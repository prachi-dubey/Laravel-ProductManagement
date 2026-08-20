<?php

namespace App\Interfaces\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function hasProducts(Category $category): bool;

    /**
     * @param  list<string>  $relations
     * @param  list<string>  $withCount
     */
    public function loadRelations(Category $category, array $relations = [], array $withCount = ['products']): Category;
}

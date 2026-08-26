<?php

namespace App\Interfaces\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(array $filters): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;

    /**
     * @param  list<int>  $categoryIds
     */
    public function syncCategories(Product $product, array $categoryIds): void;

    public function hasOrderItems(Product $product): bool;

    public function findForUpdate(int $id): ?Product;

    public function decrementStock(Product $product, int $quantity): void;

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Product $product, array $relations = ['categories']): Product;
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateCatalog(array $filters, int $perPage): LengthAwarePaginator;

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
     * @param  list<int>  $tagIds
     */
    public function syncTags(Product $product, array $tagIds): void;

    public function hasOrderItems(Product $product): bool;

    public function findForUpdate(int $id): ?Product;

    public function decrementStock(Product $product, int $quantity): void;

    /**
     * @param  list<string>  $relations
     */
    public function loadRelations(Product $product, array $relations = ['category', 'tags']): Product;
}

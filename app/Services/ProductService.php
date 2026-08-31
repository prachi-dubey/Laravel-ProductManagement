<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Interfaces\Product\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function index(array $filters): LengthAwarePaginator
    {
        return $this->products->index($filters);
    }

    public function show(Product $product): Product
    {
        return $this->products->loadRelations($product, ['categories']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $categoryIds = $data['category_ids'];
        unset($data['category_ids']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        $product = $this->products->create($data);
        $this->products->syncCategories($product, $categoryIds);

        if ($image instanceof UploadedFile) {
            $this->storeImage($product, $image);
        }

        return $this->products->loadRelations($product);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $image = $data['image'] ?? null;
        $removeImage = (bool) ($data['remove_image'] ?? false);
        unset($data['image'], $data['remove_image']);

        $categoryIds = array_key_exists('category_ids', $data) ? $data['category_ids'] : null;
        unset($data['category_ids']);

        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->products->update($product, $data);

        if (is_array($categoryIds)) {
            $this->products->syncCategories($product, $categoryIds);
        }

        if ($image instanceof UploadedFile) {
            $this->storeImage($product, $image);
        } elseif ($removeImage) {
            $this->clearImage($product);
        }

        return $this->products->loadRelations($product);
    }

    public function delete(Product $product): void
    {
        if ($this->products->hasOrderItems($product)) {
            throw ApiException::productInUse();
        }

        $this->deleteStoredImage($product->image_path);
        $this->products->delete($product);
    }

    /**
     * @param  list<int>  $categoryIds
     */
    public function syncCategories(Product $product, array $categoryIds): Product
    {
        $this->products->syncCategories($product, $categoryIds);

        return $this->products->loadRelations($product);
    }

    private function storeImage(Product $product, UploadedFile $file): void
    {
        $path = $file->store('products', 'public');

        $this->deleteStoredImage($product->image_path);
        $this->products->update($product, ['image_path' => $path]);
        $product->image_path = $path;
    }

    private function clearImage(Product $product): void
    {
        $this->deleteStoredImage($product->image_path);
        $this->products->update($product, ['image_path' => null]);
        $product->image_path = null;
    }

    private function deleteStoredImage($path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

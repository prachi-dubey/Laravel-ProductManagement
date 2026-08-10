<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Business logic for products — persistence goes through ProductRepository.
 */
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
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->products->paginateCatalog($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        $product = $this->products->create($data);

        if ($tagIds !== []) {
            $this->products->syncTags($product, $tagIds);
        }

        return $this->products->loadRelations($product);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $tagIds = array_key_exists('tag_ids', $data) ? $data['tag_ids'] : null;
        unset($data['tag_ids']);

        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->products->update($product, $data);

        if (is_array($tagIds)) {
            $this->products->syncTags($product, $tagIds);
        }

        return $this->products->loadRelations($product);
    }

    public function delete(Product $product): void
    {
        if ($this->products->hasOrderItems($product)) {
            throw ValidationException::withMessages([
                'product' => 'Cannot delete product that appears on existing orders.',
            ]);
        }

        $this->deleteStoredImage($product->image_path);
        $this->products->delete($product);
    }

    public function uploadImage(Product $product, UploadedFile $file): Product
    {
        $path = $file->store('products', 'public');

        $this->deleteStoredImage($product->image_path);

        $this->products->update($product, [
            'image_path' => $path,
        ]);

        return $this->products->loadRelations($product);
    }

    public function deleteImage(Product $product): Product
    {
        if (! $product->image_path) {
            throw ValidationException::withMessages([
                'image' => 'Product has no image to delete.',
            ]);
        }

        $this->deleteStoredImage($product->image_path);

        $this->products->update($product, [
            'image_path' => null,
        ]);

        return $this->products->loadRelations($product);
    }

    /**
     * @param  list<int>  $tagIds
     */
    public function syncTags(Product $product, array $tagIds): Product
    {
        $this->products->syncTags($product, $tagIds);

        return $this->products->loadRelations($product);
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

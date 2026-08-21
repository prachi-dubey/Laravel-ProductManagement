<?php

namespace App\Http\Resources\Api\Product;

use App\Http\Resources\Api\Category\CategoryResource;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (string) $this->price,
            'stock' => (int) $this->stock,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path
                ? $this->publicDisk()->url($this->image_path)
                : null,
            'is_active' => (bool) $this->is_active,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }

    private function publicDisk(): FilesystemAdapter
    {
        return Storage::disk('public');
    }
}

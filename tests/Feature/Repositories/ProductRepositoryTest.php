<?php

namespace Tests\Feature\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_filters_by_category_price_and_sorts_descending(): void
    {
        $gaming = Category::factory()->create(['name' => 'Gaming']);
        $office = Category::factory()->create(['name' => 'Office']);

        $low = Product::factory()->create([
            'name' => 'Budget Mouse',
            'sku' => 'SKU-LOW-001',
            'price' => 150,
            'is_active' => true,
        ]);
        $low->categories()->sync([$gaming->id]);

        $high = Product::factory()->create([
            'name' => 'Pro Mouse',
            'sku' => 'SKU-HIGH-001',
            'price' => 900,
            'is_active' => true,
        ]);
        $high->categories()->sync([$gaming->id]);

        $other = Product::factory()->create([
            'name' => 'Keyboard',
            'sku' => 'SKU-OTHER-001',
            'price' => 500,
            'is_active' => true,
        ]);
        $other->categories()->sync([$office->id]);

        $inactive = Product::factory()->create([
            'name' => 'Inactive Mouse',
            'sku' => 'SKU-INACTIVE-001',
            'price' => 700,
            'is_active' => false,
        ]);
        $inactive->categories()->sync([$gaming->id]);

        $repository = new ProductRepository();

        $paginator = $repository->paginate([
            'category_id' => $gaming->id,
            'min_price' => 100,
            'max_price' => 1000,
            'is_active' => true,
            'sort' => 'price',
            'sort_direction' => 'DESC',
            'per_page' => 10,
        ]);

        $names = collect($paginator->items())->pluck('name')->all();

        $this->assertSame(['Pro Mouse', 'Budget Mouse'], $names);
    }
}

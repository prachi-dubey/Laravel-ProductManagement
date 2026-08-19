<?php

namespace Tests\Feature\Repositories;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_applies_search_active_filter_and_case_insensitive_sort_direction(): void
    {
        Category::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
        ]);
        Category::factory()->create([
            'name' => 'Electronic Accessories',
            'slug' => 'electronic-accessories',
            'is_active' => true,
        ]);
        Category::factory()->create([
            'name' => 'Books',
            'slug' => 'books',
            'is_active' => false,
        ]);

        $repository = new CategoryRepository();

        $paginator = $repository->paginate([
            'search' => 'elect',
            'is_active' => true,
            'sort' => 'name',
            'sort_direction' => 'AsC',
        ], 10);

        $names = collect($paginator->items())->pluck('name')->all();

        $this->assertSame(['Electronic Accessories', 'Electronics'], $names);
    }
}

<?php

namespace Tests\Unit\Services;

use App\Exceptions\ApiException;
use App\Interfaces\Category\CategoryRepositoryInterface;
use App\Models\Category;
use App\Services\CategoryService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_create_generates_slug_and_defaults_is_active(): void
    {
        $category = new Category([
            'name' => 'Office Supplies',
            'slug' => 'office-supplies',
            'is_active' => true,
        ]);

        $repository = Mockery::mock(CategoryRepositoryInterface::class);
        $repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['name'] === 'Office Supplies'
                    && $payload['slug'] === 'office-supplies'
                    && $payload['is_active'] === true;
            }))
            ->andReturn($category);

        $repository
            ->shouldReceive('loadRelations')
            ->once()
            ->with($category, [], ['products'])
            ->andReturn($category);

        $service = new CategoryService($repository);

        $result = $service->create([
            'name' => 'Office Supplies',
        ]);

        $this->assertSame($category, $result);
    }

    public function test_delete_throws_when_category_has_products(): void
    {
        $category = new Category([
            'name' => 'Linked Category',
        ]);

        $repository = Mockery::mock(CategoryRepositoryInterface::class);
        $repository
            ->shouldReceive('hasProducts')
            ->once()
            ->with($category)
            ->andReturn(true);
        $repository->shouldNotReceive('delete');

        $service = new CategoryService($repository);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(__('messages.categories.in_use'));

        $service->delete($category);
    }
}

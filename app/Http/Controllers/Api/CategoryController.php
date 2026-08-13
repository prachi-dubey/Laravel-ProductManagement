<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\IndexCategoryRequest;
use App\Http\Requests\Api\Category\SaveCategoryRequest;
use App\Http\Resources\Api\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Helper\ApiListHelper;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /** @var CategoryService */
    private $categories;

    public function __construct(CategoryService $categories)
    {
        $this->categories = $categories;
    }

    public function index(IndexCategoryRequest $request): JsonResponse
    {
        $perPage = ApiListHelper::perPage($request)['per_page'];
        $paginator = $this->categories->paginate($request->validated(), $perPage);
        $paginator->appends($request->input());

        return $this->paginated(
            CategoryResource::collection($paginator),
            __('messages.categories.listed')
        );
    }

    public function show(Category $category): JsonResponse
    {
        $category = $this->categories->show($category);

        return $this->success(
            __('messages.categories.shown'),
            new CategoryResource($category)
        );
    }

    public function store(SaveCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = $this->categories->create($request->validated());

        return $this->success(
            __('messages.categories.created'),
            new CategoryResource($category),
            201
        );
    }

    public function update(SaveCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        $category = $this->categories->update($category, $request->validated());

        return $this->success(
            __('messages.categories.updated'),
            new CategoryResource($category)
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categories->delete($category);

        return $this->success(__('messages.categories.deleted'));
    }
}

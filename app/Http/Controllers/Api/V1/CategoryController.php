<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CategoryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->withCount('courses')
            ->when($request->boolean('with_children'), fn ($q) => $q->with('children'))
            ->roots()
            ->ordered()
            ->get();

        return $this->success(CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $category = Category::query()->create($request->validated());

        return $this->created(new CategoryResource($category), 'Category created successfully');
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('courses')->load('parent', 'children');

        return $this->success(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);
        $category->update($request->validated());

        return $this->success(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $category->delete();

        return $this->noContent();
    }
}

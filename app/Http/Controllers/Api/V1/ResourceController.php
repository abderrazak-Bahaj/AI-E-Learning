<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreResourceRequest;
use App\Http\Requests\Api\V1\UpdateResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Course;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;

final class ResourceController extends ApiController
{
    public function index(Course $course): JsonResponse
    {
        $resources = $course->resources()->orderBy('order')->get();

        return $this->success(ResourceResource::collection($resources));
    }

    public function store(StoreResourceRequest $request, Course $course): JsonResponse
    {
        $this->authorize('create', Resource::class);

        $resource = $course->resources()->create($request->validated());

        return $this->created(new ResourceResource($resource), 'Resource created successfully');
    }

    public function show(Course $course, Resource $resource): JsonResponse
    {
        return $this->success(new ResourceResource($resource));
    }

    public function update(UpdateResourceRequest $request, Course $course, Resource $resource): JsonResponse
    {
        $this->authorize('update', $resource);
        $resource->update($request->validated());

        return $this->success(new ResourceResource($resource), 'Resource updated successfully');
    }

    public function destroy(Course $course, Resource $resource): JsonResponse
    {
        $this->authorize('delete', $resource);
        $resource->delete();

        return $this->noContent();
    }
}

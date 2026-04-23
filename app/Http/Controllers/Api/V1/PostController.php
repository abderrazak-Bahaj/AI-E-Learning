<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

final class PostController extends ApiController
{
    /**
     * List all posts with author and comment count.
     */
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->with('user')
            ->withCount('comments')
            ->latest()
            ->paginate(15);
        Log::info('here we go');
        return PostResource::collection($posts);
    }

    /**
     * Create a new post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $post = $user->posts()->create($request->validated());

        $post->load('user');

        return $this->created(new PostResource($post), 'Post created successfully');
    }

    /**
     * Get a single post with its comments.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'comments.user'])->loadCount('comments');

        return $this->success(new PostResource($post));
    }

    /**
     * Update a post. Only the owner can update.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if ($request->user()->cannot('update', $post)) {
            return $this->forbidden('You do not own this post');
        }

        $post->update($request->validated());

        return $this->success(new PostResource($post), 'Post updated successfully');
    }

    /**
     * Delete a post. Only the owner can delete.
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        if ($request->user()->cannot('update', $post)) {
            return $this->forbidden('You do not own this post');
        }

        $post->delete();

        return $this->noContent();
    }
}

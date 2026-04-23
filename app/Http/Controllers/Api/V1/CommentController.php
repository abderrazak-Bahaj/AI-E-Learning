<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreCommentRequest;
use App\Http\Requests\Api\V1\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CommentController extends ApiController
{
    /**
     * List all comments for a post.
     */
    public function index(Post $post): AnonymousResourceCollection
    {
        $comments = $post->comments()
            ->with('user')
            ->latest()
            ->paginate(15);

        return CommentResource::collection($comments);
    }

    /**
     * Add a comment to a post.
     */
    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $comment = $post->comments()->create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        $comment->load('user');

        return $this->created(new CommentResource($comment), 'Comment added successfully');
    }

    /**
     * Get a single comment.
     */
    public function show(Post $post, Comment $comment): JsonResponse
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        $comment->load('user');

        return $this->success(new CommentResource($comment));
    }

    /**
     * Update a comment. Only the owner can update.
     */
    public function update(UpdateCommentRequest $request, Post $post, Comment $comment): JsonResponse
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        if ($request->user()->cannot('update', $comment)) {
            return $this->forbidden('You do not own this comment');
        }

        $comment->update($request->validated());

        return $this->success(new CommentResource($comment), 'Comment updated successfully');
    }

    /**
     * Delete a comment. Only the owner can delete.
     */
    public function destroy(Request $request, Post $post, Comment $comment): JsonResponse
    {
        $this->ensureCommentBelongsToPost($post, $comment);

        if ($request->user()->cannot('update', $comment)) {
            return $this->forbidden('You do not own this comment');
        }

        $comment->delete();

        return $this->noContent();
    }

    private function ensureCommentBelongsToPost(Post $post, Comment $comment): void
    {
        abort_if($comment->post_id !== $post->id, 404, 'Comment not found for this post');
    }
}

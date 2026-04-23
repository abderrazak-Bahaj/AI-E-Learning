<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

describe('Comments', function (): void {
    it('lists comments for a post', function (): void {
        $post = Post::factory()->create();
        Comment::factory(3)->for($post)->for(User::factory()->create())->create();
        Passport::actingAs(User::factory()->create());

        $this->getJson("/api/v1/posts/{$post->id}/comments")
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('adds a comment to a post', function (): void {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", [
            'body' => 'Great post!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'Great post!')
            ->assertJsonPath('message', 'Comment added successfully');

        $this->assertDatabaseHas('comments', ['body' => 'Great post!', 'post_id' => $post->id, 'user_id' => $user->id]);
    });

    it('fails to add comment with missing body', function (): void {
        $post = Post::factory()->create();
        Passport::actingAs(User::factory()->create());

        $this->postJson("/api/v1/posts/{$post->id}/comments", [])->assertStatus(422);
    });

    it('shows a comment', function (): void {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->for(User::factory()->create())->create();
        Passport::actingAs(User::factory()->create());

        $this->getJson("/api/v1/posts/{$post->id}/comments/{$comment->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $comment->id);
    });

    it('updates own comment', function (): void {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->for($user)->create();
        Passport::actingAs($user);

        $this->putJson("/api/v1/posts/{$post->id}/comments/{$comment->id}", ['body' => 'Updated!'])
            ->assertStatus(200)
            ->assertJsonPath('data.body', 'Updated!');
    });

    it('cannot update another users comment', function (): void {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->for(User::factory()->create())->create();
        Passport::actingAs(User::factory()->create());

        $this->putJson("/api/v1/posts/{$post->id}/comments/{$comment->id}", ['body' => 'Hacked'])
            ->assertStatus(403);
    });

    it('deletes own comment', function (): void {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->for($user)->create();
        Passport::actingAs($user);

        $this->deleteJson("/api/v1/posts/{$post->id}/comments/{$comment->id}")->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    it('cannot delete another users comment', function (): void {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->for(User::factory()->create())->create();
        Passport::actingAs(User::factory()->create());

        $this->deleteJson("/api/v1/posts/{$post->id}/comments/{$comment->id}")->assertStatus(403);
    });
});

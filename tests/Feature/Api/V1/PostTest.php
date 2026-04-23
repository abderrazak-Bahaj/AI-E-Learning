<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

describe('Posts', function (): void {
    it('lists all posts', function (): void {
        $user = User::factory()->create();
        Post::factory(3)->for($user)->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('creates a post', function (): void {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/posts', [
            'title' => 'My first post',
            'body' => 'Hello world content here.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My first post')
            ->assertJsonPath('message', 'Post created successfully');

        $this->assertDatabaseHas('posts', ['title' => 'My first post', 'user_id' => $user->id]);
    });

    it('fails to create a post with missing fields', function (): void {
        Passport::actingAs(User::factory()->create());

        $this->postJson('/api/v1/posts', [])->assertStatus(422);
    });

    it('shows a post', function (): void {
        $post = Post::factory()->create();
        Passport::actingAs(User::factory()->create());

        $this->getJson("/api/v1/posts/{$post->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $post->id);
    });

    it('updates own post', function (): void {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        Passport::actingAs($user);

        $this->putJson("/api/v1/posts/{$post->id}", ['title' => 'Updated', 'body' => 'Updated body'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated');
    });

    it('cannot update another users post', function (): void {
        $post = Post::factory()->create();
        Passport::actingAs(User::factory()->create());

        $this->putJson("/api/v1/posts/{$post->id}", ['title' => 'Hacked', 'body' => 'Hacked body'])
            ->assertStatus(403);
    });

    it('deletes own post', function (): void {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        Passport::actingAs($user);

        $this->deleteJson("/api/v1/posts/{$post->id}")->assertStatus(204);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    });

    it('cannot delete another users post', function (): void {
        $post = Post::factory()->create();
        Passport::actingAs(User::factory()->create());

        $this->deleteJson("/api/v1/posts/{$post->id}")->assertStatus(403);
    });
});

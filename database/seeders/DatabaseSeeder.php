<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin / demo user with known credentials
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 9 random users, each with posts and comments
        $users = User::factory(9)->create([
            'email_verified_at' => now(),
        ]);

        $allUsers = $users->prepend($admin);

        // Each user gets 3 posts
        $allUsers->each(function (User $user) use ($allUsers): void {
            Post::factory(3)
                ->for($user)
                ->create()
                ->each(function (Post $post) use ($allUsers): void {
                    // Each post gets 5 comments from random users
                    Comment::factory(5)
                        ->for($post)
                        ->for($allUsers->random())
                        ->create();
                });
        });

        $this->command->info('Seeded: 10 users, 30 posts, 150 comments');
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Roles must exist before users are created
        $this->call(RoleSeeder::class);

        // 1. Users (admin, teachers, students)
        if (User::count() === 0) {
            $this->call(UserSeeder::class);
        } else {
            $this->command->info('Users already exist — skipping UserSeeder.');
        }

        // 2. Categories
        if (Category::count() === 0) {
            CategoryFactory::new()->createPredefined();
            $this->command->info('Seeded: 12 categories');
        } else {
            $this->command->info('Categories already exist — skipping.');
        }

        // 3. Courses + lessons + resources + assignments
        if (Course::count() === 0) {
            $this->call(CourseSeeder::class);
        } else {
            $this->command->info('Courses already exist — skipping CourseSeeder.');
        }

        // 4. Enrollments + lesson progress + certificates
        $this->call(EnrollmentSeeder::class);

        // 5. Invoices + payments
        $this->call(PaymentSeeder::class);

        // 6. Submissions + answers
        $this->call(SubmissionSeeder::class);

        $this->command->info('Database seeding complete.');
    }
}

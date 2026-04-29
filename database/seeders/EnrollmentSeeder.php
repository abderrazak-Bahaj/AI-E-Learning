<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::where('status', 'PUBLISHED')->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->warn('No students or courses found. Run UserSeeder and CourseSeeder first.');

            return;
        }

        $enrollmentCount = 0;
        $progressCount = 0;
        $certificateCount = 0;

        foreach ($students as $student) {
            // Each student enrolls in 2–4 random courses
            $selectedCourses = $courses->random(min(fake()->numberBetween(2, 4), $courses->count()));

            foreach ($selectedCourses as $course) {
                // Skip if already enrolled
                $alreadyEnrolled = Enrollment::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ($alreadyEnrolled) {
                    continue;
                }

                $isCompleted = fake()->boolean(30);
                $enrolledAt = fake()->dateTimeBetween('-1 year', '-1 month');

                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => $isCompleted ? 'COMPLETED' : 'ACTIVE',
                    'progress' => $isCompleted ? 100 : fake()->randomFloat(2, 5, 90),
                    'enrolled_at' => $enrolledAt,
                    'completed_at' => $isCompleted
                        ? fake()->dateTimeBetween($enrolledAt, 'now')
                        : null,
                ]);

                $enrollmentCount++;

                // Seed lesson progress for this enrollment
                $progressCount += $this->seedLessonProgress($enrollment, $isCompleted);

                // Issue certificate for completed enrollments
                if ($isCompleted) {
                    Certificate::firstOrCreate(
                        ['student_id' => $student->id, 'course_id' => $course->id],
                        [
                            'certificate_number' => 'CERT-'.mb_strtoupper(Str::random(10)),
                            'enrollment_id' => $enrollment->id,
                            'status' => 'GENERATED',
                            'certificate_url' => 'https://example.com/certificates/'.Str::uuid().'.pdf',
                            'metadata' => [
                                'grade' => 'PASS',
                                'completion_date' => $enrollment->completed_at?->format('Y-m-d'),
                                'course_title' => $course->title,
                            ],
                            'issue_date' => $enrollment->completed_at,
                            'generated_at' => $enrollment->completed_at,
                        ]
                    );
                    $certificateCount++;
                }
            }
        }

        $this->command->info("Seeded: {$enrollmentCount} enrollments, {$progressCount} lesson progress records, {$certificateCount} certificates");
    }

    private function seedLessonProgress(Enrollment $enrollment, bool $courseCompleted): int
    {
        $lessons = Lesson::where('course_id', $enrollment->course_id)
            ->orderBy('order')
            ->get();

        if ($lessons->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($lessons as $index => $lesson) {
            // Determine status based on course completion and lesson position
            if ($courseCompleted) {
                $status = 'COMPLETED';
            } elseif ($index === 0) {
                $status = fake()->randomElement(['IN_PROGRESS', 'COMPLETED']);
            } elseif ($index < $lessons->count() / 2) {
                $status = fake()->randomElement(['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED']);
            } else {
                $status = fake()->randomElement(['NOT_STARTED', 'NOT_STARTED', 'IN_PROGRESS']);
            }

            $startedAt = $status !== 'NOT_STARTED'
                ? fake()->dateTimeBetween($enrollment->enrolled_at, 'now')
                : null;

            LessonProgress::firstOrCreate(
                ['student_id' => $enrollment->student_id, 'lesson_id' => $lesson->id],
                [
                    'course_id' => $enrollment->course_id,
                    'status' => $status,
                    'watch_time' => $status === 'NOT_STARTED' ? 0 : fake()->numberBetween(60, $lesson->duration * 60),
                    'last_position' => $status === 'NOT_STARTED' ? 0 : fake()->numberBetween(30, $lesson->duration * 60),
                    'started_at' => $startedAt,
                    'completed_at' => $status === 'COMPLETED'
                        ? fake()->dateTimeBetween($startedAt ?? $enrollment->enrolled_at, 'now')
                        : null,
                ]
            );

            $count++;
        }

        return $count;
    }
}

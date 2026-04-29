<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use Illuminate\Database\Seeder;

final class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $submissionCount = 0;
        $answerCount = 0;

        $assignments = Assignment::where('status', 'PUBLISHED')->with('questions.options')->get();

        foreach ($assignments as $assignment) {
            if ($assignment->questions->isEmpty()) {
                continue;
            }

            // Get students enrolled in this course
            $enrollments = Enrollment::where('course_id', $assignment->course_id)
                ->where('status', 'ACTIVE')
                ->get();

            foreach ($enrollments as $enrollment) {
                // 60% of enrolled students attempt the assignment
                if (! fake()->boolean(60)) {
                    continue;
                }

                $isGraded = fake()->boolean(70);
                $score = $isGraded ? fake()->randomFloat(1, 40, 100) : null;

                $submission = Submission::create([
                    'student_id' => $enrollment->student_id,
                    'assignment_id' => $assignment->id,
                    'attempt_number' => 1,
                    'score' => $score,
                    'is_passed' => $score !== null && $score >= $assignment->passing_score,
                    'feedback' => $isGraded ? fake()->sentence() : null,
                    'status' => $isGraded ? 'GRADED' : 'SUBMITTED',
                    'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
                ]);

                $submissionCount++;

                // Create an answer for each question
                foreach ($assignment->questions as $question) {
                    $selectedOption = null;
                    $answerText = null;
                    $isCorrect = null;

                    if (in_array($question->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE'])) {
                        $selectedOption = $question->options->random();
                        $isCorrect = $selectedOption->is_correct;
                    } else {
                        $answerText = fake()->paragraph();
                    }

                    SubmissionAnswer::create([
                        'submission_id' => $submission->id,
                        'question_id' => $question->id,
                        'selected_option_id' => $selectedOption?->id,
                        'answer' => $answerText,
                        'is_correct' => $isCorrect,
                        'score' => $isGraded ? fake()->randomFloat(1, 0, $question->points) : null,
                        'feedback' => $isGraded ? fake()->optional(0.4)->sentence() : null,
                    ]);

                    $answerCount++;
                }
            }
        }

        $this->command->info("Seeded: {$submissionCount} submissions, {$answerCount} answers");
    }
}

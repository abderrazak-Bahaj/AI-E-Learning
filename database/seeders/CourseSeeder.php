<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentOption;
use App\Models\AssignmentQuestion;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CourseSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private array $courses = [
        [
            'title' => 'Complete Web Development Bootcamp',
            'description' => 'Become a full-stack web developer. Learn HTML, CSS, JavaScript, React, Node.js, and more with hands-on projects.',
            'category' => 'Web Development',
            'price' => 99.99,
            'level' => 'BEGINNER',
            'duration' => 2700,
            'language' => 'English',
            'skills' => ['HTML', 'CSS', 'JavaScript', 'React', 'Node.js', 'MongoDB'],
            'lessons' => [
                ['title' => 'Introduction to HTML', 'section' => 1, 'order' => 1, 'duration' => 45, 'is_free_preview' => true],
                ['title' => 'CSS Fundamentals & Flexbox', 'section' => 1, 'order' => 2, 'duration' => 60],
                ['title' => 'JavaScript Basics', 'section' => 2, 'order' => 3, 'duration' => 75],
                ['title' => 'DOM Manipulation', 'section' => 2, 'order' => 4, 'duration' => 55],
                ['title' => 'Introduction to React', 'section' => 3, 'order' => 5, 'duration' => 80],
                ['title' => 'Node.js & Express', 'section' => 3, 'order' => 6, 'duration' => 70],
            ],
        ],
        [
            'title' => 'Machine Learning A-Z',
            'description' => 'Learn to create Machine Learning algorithms in Python. Covers regression, classification, clustering, and deep learning.',
            'category' => 'Data Science',
            'price' => 129.99,
            'level' => 'INTERMEDIATE',
            'duration' => 2520,
            'language' => 'English',
            'skills' => ['Python', 'Scikit-Learn', 'TensorFlow', 'Pandas', 'NumPy'],
            'lessons' => [
                ['title' => 'Python for Data Science', 'section' => 1, 'order' => 1, 'duration' => 60, 'is_free_preview' => true],
                ['title' => 'Data Preprocessing', 'section' => 1, 'order' => 2, 'duration' => 50],
                ['title' => 'Regression Models', 'section' => 2, 'order' => 3, 'duration' => 70],
                ['title' => 'Classification Algorithms', 'section' => 2, 'order' => 4, 'duration' => 65],
                ['title' => 'Neural Networks Basics', 'section' => 3, 'order' => 5, 'duration' => 80],
            ],
        ],
        [
            'title' => 'UI/UX Design Masterclass',
            'description' => 'Master user interface and user experience design. Learn Figma, wireframing, prototyping, and design systems.',
            'category' => 'Design',
            'price' => 89.99,
            'level' => 'BEGINNER',
            'duration' => 1560,
            'language' => 'English',
            'skills' => ['Figma', 'Wireframing', 'Prototyping', 'Design Systems', 'User Research'],
            'lessons' => [
                ['title' => 'Design Thinking Fundamentals', 'section' => 1, 'order' => 1, 'duration' => 40, 'is_free_preview' => true],
                ['title' => 'Color Theory & Typography', 'section' => 1, 'order' => 2, 'duration' => 50],
                ['title' => 'Wireframing with Figma', 'section' => 2, 'order' => 3, 'duration' => 65],
                ['title' => 'Prototyping & User Testing', 'section' => 2, 'order' => 4, 'duration' => 55],
                ['title' => 'Building a Design System', 'section' => 3, 'order' => 5, 'duration' => 70],
            ],
        ],
        [
            'title' => 'Digital Marketing Masterclass',
            'description' => 'Learn SEO, social media marketing, email marketing, Google Ads, and analytics to grow any business online.',
            'category' => 'Marketing',
            'price' => 79.99,
            'level' => 'BEGINNER',
            'duration' => 1800,
            'language' => 'English',
            'skills' => ['SEO', 'Google Ads', 'Social Media', 'Email Marketing', 'Analytics'],
            'lessons' => [
                ['title' => 'Digital Marketing Overview', 'section' => 1, 'order' => 1, 'duration' => 35, 'is_free_preview' => true],
                ['title' => 'SEO Fundamentals', 'section' => 1, 'order' => 2, 'duration' => 60],
                ['title' => 'Social Media Strategy', 'section' => 2, 'order' => 3, 'duration' => 55],
                ['title' => 'Email Marketing Campaigns', 'section' => 2, 'order' => 4, 'duration' => 50],
                ['title' => 'Google Analytics & Reporting', 'section' => 3, 'order' => 5, 'duration' => 45],
            ],
        ],
        [
            'title' => 'AWS Solutions Architect',
            'description' => 'Prepare for the AWS Solutions Architect Associate exam. Covers EC2, S3, VPC, RDS, Lambda, and cloud architecture.',
            'category' => 'IT & Software',
            'price' => 129.99,
            'level' => 'INTERMEDIATE',
            'duration' => 1740,
            'language' => 'English',
            'skills' => ['AWS', 'EC2', 'S3', 'VPC', 'RDS', 'Lambda', 'Cloud Architecture'],
            'lessons' => [
                ['title' => 'AWS Core Concepts', 'section' => 1, 'order' => 1, 'duration' => 50, 'is_free_preview' => true],
                ['title' => 'EC2 & Auto Scaling', 'section' => 1, 'order' => 2, 'duration' => 70],
                ['title' => 'S3 & Storage Solutions', 'section' => 2, 'order' => 3, 'duration' => 60],
                ['title' => 'VPC & Networking', 'section' => 2, 'order' => 4, 'duration' => 75],
                ['title' => 'RDS & Database Services', 'section' => 3, 'order' => 5, 'duration' => 65],
            ],
        ],
    ];

    public function run(): void
    {
        $teachers = User::role('teacher')->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found. Run UserSeeder first.');

            return;
        }

        foreach ($this->courses as $index => $data) {
            $category = Category::where('name', $data['category'])->first();

            if (! $category) {
                $this->command->warn("Category '{$data['category']}' not found, skipping.");

                continue;
            }

            $teacher = $teachers[$index % $teachers->count()];

            $course = Course::firstOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'status' => 'PUBLISHED',
                    'level' => $data['level'],
                    'duration' => $data['duration'],
                    'language' => $data['language'],
                    'skills' => $data['skills'],
                    'category_id' => $category->id,
                    'teacher_id' => $teacher->id,
                ]
            );

            // Skip if already has lessons
            if ($course->wasRecentlyCreated) {
                $this->seedLessons($course, $data['lessons']);
                $this->seedResources($course);
                $this->seedAssignment($course);
            }
        }

        $this->command->info('Seeded: '.count($this->courses).' courses with lessons, resources, and assignments');
    }

    /** @param array<int, array<string, mixed>> $lessonsData */
    private function seedLessons(Course $course, array $lessonsData): void
    {
        foreach ($lessonsData as $lessonData) {
            Lesson::create([
                'title' => $lessonData['title'],
                'content' => fake()->paragraphs(3, true),
                'video_url' => 'https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'order' => $lessonData['order'],
                'section' => $lessonData['section'],
                'duration' => $lessonData['duration'],
                'is_free_preview' => $lessonData['is_free_preview'] ?? false,
                'status' => 'PUBLISHED',
                'course_id' => $course->id,
            ]);
        }

        // Attach 2 PDF resources to the first lesson
        $firstLesson = $course->lessons()->orderBy('order')->first();
        if ($firstLesson) {
            Resource::create([
                'title' => $course->title.' — Starter Guide',
                'file_url' => 'https://example.com/resources/starter-guide.pdf',
                'type' => 'PDF',
                'order' => 1,
                'is_preview' => true,
                'course_id' => $course->id,
                'lesson_id' => $firstLesson->id,
            ]);
        }
    }

    private function seedResources(Course $course): void
    {
        // Course-level resource (no lesson)
        Resource::create([
            'title' => $course->title.' — Full Cheat Sheet',
            'file_url' => 'https://example.com/resources/cheat-sheet.pdf',
            'type' => 'PDF',
            'order' => 1,
            'is_preview' => false,
            'course_id' => $course->id,
            'lesson_id' => null,
        ]);
    }

    private function seedAssignment(Course $course): void
    {
        $assignment = Assignment::create([
            'title' => 'Final Quiz: '.$course->title,
            'description' => 'Test your knowledge of the key concepts covered in this course.',
            'type' => 'MULTIPLE_CHOICE',
            'time_limit' => 30,
            'max_attempts' => 2,
            'total_points' => 30,
            'passing_score' => 60,
            'status' => 'PUBLISHED',
            'course_id' => $course->id,
            'lesson_id' => null,
        ]);

        // 3 multiple-choice questions
        $questions = [
            ['text' => 'Which of the following best describes the main topic of this course?', 'points' => 10],
            ['text' => 'What is the recommended approach when starting a new project in this field?', 'points' => 10],
            ['text' => 'Which tool or technology is most commonly used in this domain?', 'points' => 10],
        ];

        foreach ($questions as $order => $q) {
            $question = AssignmentQuestion::create([
                'question_text' => $q['text'],
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => $q['points'],
                'order' => $order + 1,
                'explanation' => 'Review the relevant lesson for more details.',
                'assignment_id' => $assignment->id,
            ]);

            // 4 options, first is correct
            $options = [
                ['text' => 'The correct answer based on course content', 'is_correct' => true],
                ['text' => 'An incorrect but plausible option', 'is_correct' => false],
                ['text' => 'Another incorrect option', 'is_correct' => false],
                ['text' => 'A clearly wrong option', 'is_correct' => false],
            ];

            foreach ($options as $optOrder => $opt) {
                AssignmentOption::create([
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                    'order' => $optOrder + 1,
                    'question_id' => $question->id,
                ]);
            }
        }
    }
}

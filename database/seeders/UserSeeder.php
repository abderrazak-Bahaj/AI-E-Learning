<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'status' => 'ACTIVE',
                'phone' => '+1-555-000-0001',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        Admin::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'department' => 'IT Department',
                'position' => 'System Administrator',
                'permissions' => ['manage_users', 'manage_courses', 'manage_settings', 'manage_payments'],
                'super_admin' => true,
            ]
        );

        // ── Teachers ──────────────────────────────────────────────────────────
        $teacherData = [
            [
                'user' => [
                    'name' => 'Sarah Johnson',
                    'email' => 'teacher1@example.com',
                    'phone' => '+1-555-000-0010',
                ],
                'profile' => [
                    'specialization' => 'Web Development',
                    'qualification' => 'MSc in Computer Science',
                    'expertise' => 'Full-stack development, React, Node.js, Laravel',
                    'education' => [
                        ['degree' => 'MSc', 'field' => 'Computer Science', 'institution' => 'MIT', 'year' => 2015],
                    ],
                    'certifications' => [
                        ['name' => 'AWS Certified Developer', 'year' => 2020],
                        ['name' => 'Google Cloud Professional', 'year' => 2022],
                    ],
                    'rating' => 4.85,
                    'years_of_experience' => 10,
                ],
            ],
            [
                'user' => [
                    'name' => 'Michael Chen',
                    'email' => 'teacher2@example.com',
                    'phone' => '+1-555-000-0011',
                ],
                'profile' => [
                    'specialization' => 'Data Science & AI',
                    'qualification' => 'PhD in Machine Learning',
                    'expertise' => 'Machine Learning, Deep Learning, Python, Data Analysis',
                    'education' => [
                        ['degree' => 'PhD', 'field' => 'Machine Learning', 'institution' => 'Stanford University', 'year' => 2018],
                    ],
                    'certifications' => [
                        ['name' => 'TensorFlow Developer Certificate', 'year' => 2021],
                    ],
                    'rating' => 4.92,
                    'years_of_experience' => 8,
                ],
            ],
        ];

        foreach ($teacherData as $data) {
            $teacher = User::firstOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], [
                    'password' => Hash::make('password'),
                    'status' => 'ACTIVE',
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                ])
            );
            $teacher->syncRoles(['teacher']);

            Teacher::firstOrCreate(
                ['user_id' => $teacher->id],
                $data['profile']
            );
        }

        // ── Students ──────────────────────────────────────────────────────────
        $studentData = [
            [
                'user' => ['name' => 'Alice Martin', 'email' => 'student1@example.com', 'phone' => '+1-555-000-0020'],
                'profile' => [
                    'student_id' => 'ST000001',
                    'education_level' => 'Undergraduate',
                    'major' => 'Computer Science',
                    'interests' => ['Web Development', 'AI', 'Mobile Apps'],
                    'date_of_birth' => '1999-03-15',
                    'learning_preferences' => ['Video', 'Self-paced'],
                    'gpa' => 3.8,
                ],
            ],
            [
                'user' => ['name' => 'Bob Williams', 'email' => 'student2@example.com', 'phone' => '+1-555-000-0021'],
                'profile' => [
                    'student_id' => 'ST000002',
                    'education_level' => 'Graduate',
                    'major' => 'Data Science',
                    'interests' => ['Data Science', 'Machine Learning', 'Python'],
                    'date_of_birth' => '1997-07-22',
                    'learning_preferences' => ['Interactive', 'Fast'],
                    'gpa' => 3.6,
                ],
            ],
            [
                'user' => ['name' => 'Clara Diaz', 'email' => 'student3@example.com', 'phone' => '+1-555-000-0022'],
                'profile' => [
                    'student_id' => 'ST000003',
                    'education_level' => 'Undergraduate',
                    'major' => 'Design',
                    'interests' => ['UI/UX Design', 'Photography', 'Marketing'],
                    'date_of_birth' => '2000-11-08',
                    'learning_preferences' => ['Video', 'Text'],
                    'gpa' => 3.9,
                ],
            ],
        ];

        foreach ($studentData as $data) {
            $student = User::firstOrCreate(
                ['email' => $data['user']['email']],
                array_merge($data['user'], [
                    'password' => Hash::make('password'),
                    'status' => 'ACTIVE',
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                ])
            );
            $student->syncRoles(['student']);

            Student::firstOrCreate(
                ['user_id' => $student->id],
                $data['profile']
            );
        }

        // ── Extra random students ──────────────────────────────────────────────
        User::factory(7)
            ->student()
            ->has(Student::factory(), 'student')
            ->create(['email_verified_at' => now()]);

        $this->command->info('Seeded: 1 admin, 2 teachers, 10 students');
    }
}

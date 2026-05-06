<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class DashboardController extends ApiController
{
    // ── Admin ──────────────────────────────────────────────────────────────────

    /**
     * Admin dashboard statistics.
     *
     * Returns comprehensive dashboard statistics matching the frontend DashboardStatistics interface.
     * Includes overview metrics, monthly stats, enrollment trends, recent enrollments, and teacher performance.
     * Cached for 5 minutes.
     */
    public function adminStats(): JsonResponse
    {
        $stats = Cache::remember('dashboard.admin', 300, function (): array {
            // Overview section - aggregated statistics
            $usersByRole = User::query()
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', User::class)
                ->selectRaw('roles.name as role, count(users.id) as total')
                ->groupBy('roles.name')
                ->pluck('total', 'role');

            $totalRevenue = Payment::query()
                ->where('status', 'COMPLETED')
                ->sum('amount');

            $overview = [
                'total_courses' => Course::query()->count(),
                'total_students' => (int) $usersByRole->get('student', 0),
                'total_teachers' => (int) $usersByRole->get('teacher', 0),
                'total_enrollments' => Enrollment::query()->count(),
                'total_revenue' => round((float) $totalRevenue, 2),
            ];

            // Monthly stats - last 6 months of revenue data
            $monthlyStats = Payment::query()
                ->where('status', 'COMPLETED')
                ->where('created_at', '>=', now()->subMonths(6))
                ->get(['created_at', 'amount'])
                ->groupBy(fn ($payment) => $payment->created_at->format('Y-m'))
                ->map(fn ($payments, $monthKey) => [
                    'month' => now()->createFromFormat('Y-m', $monthKey)->format('M Y'),
                    'total_invoices' => $payments->count(),
                    'total_revenue' => round((float) $payments->sum('amount'), 2),
                ])
                ->sortKeysDesc()
                ->values()
                ->toArray();

            // Enrollment trends - last 6 months of enrollment data
            $enrollmentTrends = Enrollment::query()
                ->where('enrolled_at', '>=', now()->subMonths(6))
                ->get(['enrolled_at'])
                ->groupBy(fn ($enrollment) => $enrollment->enrolled_at->format('Y-m'))
                ->map(fn ($enrollments, $monthKey) => [
                    'month' => now()->createFromFormat('Y-m', $monthKey)->format('M Y'),
                    'total_enrollments' => $enrollments->count(),
                ])
                ->sortKeysDesc()
                ->values()
                ->toArray();

            // Recent enrollments - 10 most recent with student and course details
            $recentEnrollments = Enrollment::query()
                ->with(['student:id,name,email,avatar', 'course:id,title,category_id'])
                ->latest('enrolled_at')
                ->limit(10)
                ->get()
                ->map(fn ($enrollment) => [
                    'id' => $enrollment->id,
                    'user' => [
                        'id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'email' => $enrollment->student->email,
                    ],
                    'course' => [
                        'id' => $enrollment->course->id,
                        'title' => $enrollment->course->title,
                    ],
                    'created_at' => $enrollment->enrolled_at->toIso8601String(),
                ])
                ->toArray();

            // Teacher performance - top 5 teachers by enrollment count
            $teacherPerformance = User::query()
                ->role('teacher')
                ->withCount(['courses', 'courses as total_enrollments' => function ($query): void {
                    $query->join('enrollments', 'courses.id', '=', 'enrollments.course_id');
                }])
                ->orderByDesc('total_enrollments')
                ->limit(5)
                ->get(['id', 'name'])
                ->map(fn ($teacher) => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'courses_count' => $teacher->courses_count,
                    'total_enrollments' => $teacher->total_enrollments,
                    'courses_avg_rating' => 0, // Placeholder - ratings not implemented yet
                ])
                ->toArray();

            // Top courses - top 5 by enrollment count with enrollments_count field
            $topCourses = Course::query()
                ->withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->limit(5)
                ->get(['id', 'title', 'price'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'enrollments_count' => $c->enrollments_count,
                ])
                ->toArray();

            return [
                'overview' => $overview,
                'monthly_stats' => $monthlyStats,
                'top_courses' => $topCourses,
                'enrollment_trends' => $enrollmentTrends,
                'recent_enrollments' => $recentEnrollments,
                'teacher_performance' => $teacherPerformance,
            ];
        });

        return $this->success($stats);
    }

    // ── Teacher ────────────────────────────────────────────────────────────────

    /**
     * Teacher dashboard statistics.
     *
     * Returns the authenticated teacher's course stats, student counts,
     * completion rate, pending submissions, and revenue. Cached for 5 minutes.
     */
    public function teacherStats(Request $request): JsonResponse
    {
        $teacherId = $request->user()->id;

        $stats = Cache::remember("dashboard.teacher.{$teacherId}", 300, function () use ($teacherId): array {
            $courseIds = Course::query()
                ->where('teacher_id', $teacherId)
                ->pluck('id');

            $totalStudents = Enrollment::query()
                ->whereIn('course_id', $courseIds)
                ->where('status', 'ACTIVE')
                ->distinct('student_id')
                ->count('student_id');

            $completedEnrollments = Enrollment::query()
                ->whereIn('course_id', $courseIds)
                ->where('status', 'COMPLETED')
                ->count();

            $totalEnrollments = Enrollment::query()
                ->whereIn('course_id', $courseIds)
                ->count();

            $avgCompletion = $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 1)
                : 0;

            $pendingSubmissions = Submission::query()
                ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->where('status', 'SUBMITTED')
                ->count();

            $revenue = Payment::query()
                ->whereIn('course_id', $courseIds)
                ->where('status', 'COMPLETED')
                ->sum('amount');

            $courses = Course::query()
                ->where('teacher_id', $teacherId)
                ->withCount('enrollments', 'lessons')
                ->latest()
                ->limit(10)
                ->get(['id', 'title', 'status', 'price'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'status' => $c->status,
                    'enrollments' => $c->enrollments_count,
                    'lessons' => $c->lessons_count,
                ]);

            return [
                'courses' => [
                    'total' => $courseIds->count(),
                    'published' => Course::query()->where('teacher_id', $teacherId)->published()->count(),
                    'list' => $courses,
                ],
                'students' => [
                    'active' => $totalStudents,
                    'completed' => $completedEnrollments,
                ],
                'completion_rate' => $avgCompletion,
                'pending_submissions' => $pendingSubmissions,
                'revenue' => [
                    'total' => round((float) $revenue, 2),
                    'currency' => 'USD',
                ],
            ];
        });

        return $this->success($stats);
    }

    // ── Student ────────────────────────────────────────────────────────────────

    /**
     * Student dashboard statistics.
     *
     * Returns the authenticated student's enrollment counts, certificates earned,
     * average score, and recent enrollments.
     */
    public function studentStats(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        $enrollments = Enrollment::query()
            ->where('student_id', $studentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $avgScore = Submission::query()
            ->where('student_id', $studentId)
            ->where('status', 'GRADED')
            ->whereNotNull('score')
            ->avg('score');

        $recentEnrollments = Enrollment::query()
            ->where('student_id', $studentId)
            ->with('course:id,title,image_url,level')
            ->latest('enrolled_at')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'course_id' => $e->course_id,
                'course_title' => $e->course?->title,
                'status' => $e->status,
                'progress' => $e->progress,
                'enrolled_at' => $e->enrolled_at?->toIso8601String(),
            ]);

        return $this->success([
            'enrollments' => [
                'total' => array_sum($enrollments->toArray()),
                'active' => $enrollments->get('ACTIVE', 0),
                'completed' => $enrollments->get('COMPLETED', 0),
                'dropped' => $enrollments->get('DROPPED', 0),
            ],
            'certificates_earned' => Certificate::query()
                ->where('student_id', $studentId)
                ->generated()
                ->count(),
            'average_score' => $avgScore ? round((float) $avgScore, 1) : null,
            'submissions' => [
                'total' => Submission::query()->where('student_id', $studentId)->count(),
                'graded' => Submission::query()->where('student_id', $studentId)->graded()->count(),
                'passed' => Submission::query()->where('student_id', $studentId)->passed()->count(),
            ],
            'recent_enrollments' => $recentEnrollments,
        ]);
    }
}

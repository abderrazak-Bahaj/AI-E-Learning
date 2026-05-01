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
     * Returns user counts by role, course stats, revenue, enrollments this month,
     * and top 5 courses by enrollment. Cached for 5 minutes.
     */
    public function adminStats(): JsonResponse
    {
        $stats = Cache::remember('dashboard.admin', 300, function (): array {
            $usersByRole = User::query()
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', User::class)
                ->selectRaw('roles.name as role, count(users.id) as total')
                ->groupBy('roles.name')
                ->pluck('total', 'role');

            $revenue = Payment::query()
                ->where('status', 'COMPLETED')
                ->sum('amount');

            $enrollmentsThisMonth = Enrollment::query()
                ->whereMonth('enrolled_at', now()->month)
                ->whereYear('enrolled_at', now()->year)
                ->count();

            $topCourses = Course::query()
                ->withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->limit(5)
                ->get(['id', 'title', 'price'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'enrollments' => $c->enrollments_count,
                ]);

            return [
                'users' => [
                    'total' => User::query()->count(),
                    'by_role' => $usersByRole,
                ],
                'courses' => [
                    'total' => Course::query()->count(),
                    'published' => Course::query()->published()->count(),
                ],
                'enrollments' => [
                    'total' => Enrollment::query()->count(),
                    'this_month' => $enrollmentsThisMonth,
                    'completed' => Enrollment::query()->completed()->count(),
                ],
                'revenue' => [
                    'total' => round((float) $revenue, 2),
                    'currency' => 'USD',
                ],
                'certificates_issued' => Certificate::query()->generated()->count(),
                'top_courses' => $topCourses,
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

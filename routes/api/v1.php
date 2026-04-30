<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\EnrollmentController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\LessonController;
use App\Http\Controllers\Api\V1\LessonProgressController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

// ── Public: Auth (brute-force protected) ──────────────────────────────────────
Route::middleware('throttle:auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->name('api.v1.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.login');
});

// ── Public: Password reset ────────────────────────────────────────────────────
Route::middleware('throttle:6,1')->group(function (): void {
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
});

// ── Public: Browse ────────────────────────────────────────────────────────────
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{course}', [CourseController::class, 'show']);
Route::get('courses/{course}/lessons', [LessonController::class, 'index']);
Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);

// ── Protected ─────────────────────────────────────────────────────────────────
Route::middleware(['auth:api', 'throttle:authenticated'])->group(function (): void {

    // ── Auth ──────────────────────────────────────────────────────────────────
    Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');
    Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::patch('profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::patch('profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    // ── Users (admin) ─────────────────────────────────────────────────────────
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // ── Categories ────────────────────────────────────────────────────────────
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // ── Courses ───────────────────────────────────────────────────────────────
    Route::get('my-courses', [CourseController::class, 'myCourses'])->name('courses.mine');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // ── Lessons (nested under course) ─────────────────────────────────────────
    Route::post('courses/{course}/lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
    Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');

    // ── Resources (nested under course) ───────────────────────────────────────
    Route::get('courses/{course}/resources', [ResourceController::class, 'index'])->name('courses.resources.index');
    Route::get('courses/{course}/resources/{resource}', [ResourceController::class, 'show'])->name('courses.resources.show');
    Route::post('courses/{course}/resources', [ResourceController::class, 'store'])->name('courses.resources.store');
    Route::put('courses/{course}/resources/{resource}', [ResourceController::class, 'update'])->name('courses.resources.update');
    Route::delete('courses/{course}/resources/{resource}', [ResourceController::class, 'destroy'])->name('courses.resources.destroy');

    // ── Assignments (nested under course) ─────────────────────────────────────
    Route::get('courses/{course}/assignments', [AssignmentController::class, 'index'])->name('courses.assignments.index');
    Route::post('courses/{course}/assignments', [AssignmentController::class, 'store'])->name('courses.assignments.store');
    Route::get('courses/{course}/assignments/{assignment}', [AssignmentController::class, 'show'])->name('courses.assignments.show');
    Route::put('courses/{course}/assignments/{assignment}', [AssignmentController::class, 'update'])->name('courses.assignments.update');
    Route::delete('courses/{course}/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('courses.assignments.destroy');

    // Assignment questions
    Route::post('courses/{course}/assignments/{assignment}/questions', [AssignmentController::class, 'storeQuestion'])
        ->name('courses.assignments.questions.store');
    Route::delete('courses/{course}/assignments/{assignment}/questions/{question}', [AssignmentController::class, 'destroyQuestion'])
        ->name('courses.assignments.questions.destroy');

    // AI: generate assignment draft (teacher reviews before saving)
    Route::post('courses/{course}/assignments/generate', [AssignmentController::class, 'generate'])
        ->name('courses.assignments.generate');

    // ── Submissions ───────────────────────────────────────────────────────────
    Route::get('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'index'])
        ->name('courses.assignments.submissions.index');
    Route::post('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
        ->name('courses.assignments.submissions.store');
    Route::get('courses/{course}/assignments/{assignment}/submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('courses.assignments.submissions.show');
    Route::patch('courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [SubmissionController::class, 'grade'])
        ->name('submissions.grade');

    // AI: pre-grade essay submission (teacher reviews before confirming)
    Route::post('courses/{course}/assignments/{assignment}/submissions/{submission}/pre-grade', [SubmissionController::class, 'preGrade'])
        ->name('submissions.pre-grade');

    // ── Enrollments ───────────────────────────────────────────────────────────
    Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

    // ── Lesson Progress ───────────────────────────────────────────────────────
    Route::get('courses/{course}/progress', [LessonProgressController::class, 'index'])->name('courses.progress.index');
    Route::patch('courses/{course}/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])
        ->name('courses.lessons.progress');

    // AI: explain lesson content to a student
    Route::post('courses/{course}/lessons/{lesson}/explain', [AiController::class, 'explainLesson'])
        ->name('courses.lessons.explain');

    // ── Certificates ──────────────────────────────────────────────────────────
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])
        ->name('certificates.download');

    // ── Invoices ──────────────────────────────────────────────────────────────
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // ── Payments ──────────────────────────────────────────────────────────────
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/create-order', [PaymentController::class, 'createOrder'])->name('payments.create-order');
    Route::post('payments/capture-order', [PaymentController::class, 'captureOrder'])->name('payments.capture-order');

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('dashboard/admin', [App\Http\Controllers\Api\V1\DashboardController::class, 'adminStats'])
        ->middleware('role:admin')
        ->name('dashboard.admin');
    Route::get('dashboard/teacher', [App\Http\Controllers\Api\V1\DashboardController::class, 'teacherStats'])
        ->middleware('role:teacher')
        ->name('dashboard.teacher');
    Route::get('dashboard/student', [App\Http\Controllers\Api\V1\DashboardController::class, 'studentStats'])
        ->middleware('role:student')
        ->name('dashboard.student');
});

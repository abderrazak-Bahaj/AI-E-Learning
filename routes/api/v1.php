<?php

declare(strict_types=1);

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

    // Auth
    Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');
    Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Profile
    Route::patch('profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::patch('profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    // Users (admin)
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy']);

    // Categories (admin write)
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);

    // Courses
    Route::get('my-courses', [CourseController::class, 'myCourses'])->name('courses.mine');
    Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);

    // Lessons (nested under course)
    Route::apiResource('courses.lessons', LessonController::class)->only(['store', 'update', 'destroy']);

    // Resources (nested under course)
    Route::apiResource('courses.resources', ResourceController::class)->except(['index', 'show']);
    Route::get('courses/{course}/resources', [ResourceController::class, 'index']);
    Route::get('courses/{course}/resources/{resource}', [ResourceController::class, 'show']);

    // Assignments (nested under course)
    Route::apiResource('courses.assignments', AssignmentController::class);
    Route::post('courses/{course}/assignments/{assignment}/questions', [AssignmentController::class, 'storeQuestion'])
        ->name('courses.assignments.questions.store');
    Route::delete('courses/{course}/assignments/{assignment}/questions/{question}', [AssignmentController::class, 'destroyQuestion'])
        ->name('courses.assignments.questions.destroy');

    // Submissions (nested under course > assignment)
    Route::get('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'index']);
    Route::post('courses/{course}/assignments/{assignment}/submissions', [SubmissionController::class, 'store']);
    Route::get('courses/{course}/assignments/{assignment}/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::patch('courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [SubmissionController::class, 'grade'])
        ->name('submissions.grade');

    // Enrollments
    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'store', 'show', 'destroy']);

    // Lesson progress (nested under course)
    Route::get('courses/{course}/progress', [LessonProgressController::class, 'index'])->name('courses.progress.index');
    Route::patch('courses/{course}/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])->name('courses.lessons.progress');

    // Certificates
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/create-order', [PaymentController::class, 'createOrder'])->name('payments.create-order');
    Route::post('payments/capture-order', [PaymentController::class, 'captureOrder'])->name('payments.capture-order');
});

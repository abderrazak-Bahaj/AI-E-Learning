<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EnrollmentCompleted;
use App\Models\Certificate;
use App\Notifications\CertificateIssued;
use App\Services\CertificateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class IssueCertificateOnCompletion implements ShouldQueue
{
    public function __construct(private readonly CertificateService $certificateService) {}

    public function handle(EnrollmentCompleted $event): void
    {
        $enrollment = $event->enrollment;

        // Idempotent — skip if certificate already exists
        $exists = Certificate::query()
            ->where('student_id', $enrollment->student_id)
            ->where('course_id', $enrollment->course_id)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            $certificate = Certificate::query()->create([
                'certificate_number' => Certificate::generateNumber(),
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'status' => 'PENDING',
                'metadata' => [
                    'course_title' => $enrollment->course->title,
                    'completion_date' => $enrollment->completed_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
                ],
            ]);

            // Generate the PDF
            $this->certificateService->generate($certificate);

            // Notify the student
            $enrollment->student->notify(new CertificateIssued($certificate->fresh()));

        } catch (Throwable $e) {
            Log::error('Certificate issuance failed', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

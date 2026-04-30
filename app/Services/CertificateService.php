<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CertificateServiceInterface;
use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

final class CertificateService implements CertificateServiceInterface
{
    /**
     * Generate the PDF for a certificate, store it, and update the model.
     * Idempotent — returns immediately if already generated.
     */
    public function generate(Certificate $certificate): Certificate
    {
        if ($certificate->isGenerated() && $certificate->certificate_url) {
            return $certificate;
        }

        $certificate->update(['status' => 'GENERATING']);

        $filename = "certificates/{$certificate->id}.pdf";
        $storagePath = Storage::disk('public')->path($filename);

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('certificates');

        $certificate->loadMissing(['student', 'course.teacher', 'enrollment']);

        Pdf::view('pdfs.certificate', ['certificate' => $certificate])
            ->landscape()
            ->format('a4')
            ->save($storagePath);

        $certificate->update([
            'status' => 'GENERATED',
            'certificate_url' => Storage::disk('public')->url($filename),
            'issue_date' => now(),
            'generated_at' => now(),
        ]);

        return $certificate->fresh();
    }

    /**
     * Return the absolute filesystem path to the certificate PDF,
     * generating it first if needed.
     */
    public function pdfPath(Certificate $certificate): string
    {
        $this->generate($certificate);

        return Storage::disk('public')->path("certificates/{$certificate->id}.pdf");
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\CertificateServiceInterface;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class CertificateController extends ApiController
{
    public function __construct(private readonly CertificateServiceInterface $certificateService) {}

    public function index(Request $request): JsonResponse
    {
        $certificates = Certificate::query()
            ->forStudent($request->user()->id)
            ->generated()
            ->with('course')
            ->latest('issue_date')
            ->get();

        return $this->success(CertificateResource::collection($certificates));
    }

    public function show(Certificate $certificate): JsonResponse
    {
        $this->authorize('view', $certificate);
        $certificate->load('course', 'enrollment');

        return $this->success(new CertificateResource($certificate));
    }

    /**
     * Download the certificate as a PDF.
     * Generates the PDF on first request, then serves from storage.
     */
    public function download(Certificate $certificate): BinaryFileResponse|JsonResponse
    {
        $this->authorize('view', $certificate);

        try {
            $path = $this->certificateService->pdfPath($certificate);

            $filename = "certificate-{$certificate->certificate_number}.pdf";

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
            ]);
        } catch (Throwable $e) {
            return $this->error('Certificate generation failed: '.$e->getMessage(), 500);
        }
    }
}

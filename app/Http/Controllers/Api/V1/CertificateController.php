<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CertificateController extends ApiController
{
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
}

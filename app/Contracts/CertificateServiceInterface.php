<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Certificate;

interface CertificateServiceInterface
{
    public function generate(Certificate $certificate): Certificate;

    public function pdfPath(Certificate $certificate): string;
}

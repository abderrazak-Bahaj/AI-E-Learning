<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceController extends ApiController
{
    /**
     * List invoices.
     *
     * Students see their own invoices. Admins see all invoices.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('filter[status]', description: 'Filter by status: PAID, PENDING, CANCELLED.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('sort', description: 'Sort field: created_at, total_amount.', type: 'string', example: 'created_at')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('order', description: 'Sort direction: asc or desc.', type: 'string', example: 'desc')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('include', description: 'Include relations: courses, user, payments.', type: 'string')]
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
    public function index(Request $request): JsonResponse
    {
        $query = QueryBuilder::for(Invoice::class);

        if ($request->user()->isAdmin()) {
            // Admins can see all invoices
        } else {
            // Students see only their own invoices
            $query->forUser($request->user()->id);
        }

        return $this->paginatedResponse(
            query: $query,
            request: $request,
            resourceClass: InvoiceResource::class,
            allowedSorts: ['created_at', 'total_amount'],
            allowedFilters: ['status'],
            allowedIncludes: ['courses', 'user', 'payments'],
        );
    }

    /**
     * Get a single invoice with courses and payments.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);
        $invoice->load('courses', 'payments', 'user');

        return $this->success(new InvoiceResource($invoice));
    }

    /**
     * Return the invoice as an inline PDF.
     */
    /**
     * Download an invoice as a PDF.
     *
     * Returns Content-Type: application/pdf.
     */
    public function print(Invoice $invoice): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice->loadMissing(['courses', 'user']);

        $filename = "invoice-{$invoice->invoice_number}.pdf";

        return Pdf::view('pdfs.invoice', ['invoice' => $invoice])
            ->format('a4')
            ->name($filename);
    }
}

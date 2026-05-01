<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceController extends ApiController
{
    /**
     * List invoices.
     *
     * Students see their own invoices. Admins see all invoices.
     */
    #[\Dedoc\Scramble\Attributes\QueryParameter('per_page', description: 'Items per page (max 100).', type: 'integer', default: 15)]
    #[\Dedoc\Scramble\Attributes\QueryParameter('page', description: 'Page number.', type: 'integer', default: 1)]
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()->with('courses');

        if ($request->user()->isAdmin()) {
            $query->with('user');
        } else {
            $query->forUser($request->user()->id);
        }

        return $this->success(InvoiceResource::collection($query->latest()->paginate(15)));
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

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InvoiceController extends ApiController
{
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

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);
        $invoice->load('courses', 'payments', 'user');

        return $this->success(new InvoiceResource($invoice));
    }
}

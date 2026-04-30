<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Provides consistent pagination, search, sort, and filter for list endpoints.
 *
 * Usage in a controller:
 *   return $this->paginatedResponse(
 *       query: Course::query()->with('category'),
 *       request: $request,
 *       resourceClass: CourseResource::class,
 *       searchColumns: ['title', 'description'],
 *       allowedSorts: ['title', 'price', 'created_at'],
 *       allowedFilters: ['status', 'level', 'category_id'],
 *   );
 */
trait HasPagination
{
    /**
     * @param  class-string<JsonResource>  $resourceClass
     * @param  array<string>  $searchColumns  Columns to apply the `search` query param to
     * @param  array<string>  $allowedSorts  Columns that may be sorted
     * @param  array<string>  $allowedFilters  Columns that may be filtered via filter[column]=value
     */
    protected function paginatedResponse(
        Builder $query,
        Request $request,
        string $resourceClass,
        array $searchColumns = [],
        array $allowedSorts = ['created_at'],
        array $allowedFilters = [],
    ): JsonResponse {
        // ── Search ────────────────────────────────────────────────────────────
        $search = $request->string('search')->trim()->toString();

        if ($search !== '' && $searchColumns !== []) {
            $query->where(function (Builder $q) use ($search, $searchColumns): void {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // ── Filters ───────────────────────────────────────────────────────────
        foreach ($allowedFilters as $column) {
            $value = $request->input("filter.{$column}") ?? $request->input("filter[{$column}]");

            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        // ── Sort ──────────────────────────────────────────────────────────────
        $sort = $request->string('sort')->toString();
        $order = $request->string('order')->lower()->toString();

        if ($sort !== '' && in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        // ── Paginate ──────────────────────────────────────────────────────────
        $perPage = min((int) $request->input('per_page', 15), 100);
        $perPage = max($perPage, 1);

        $paginated = $query->paginate($perPage)->withQueryString();

        $collection = $resourceClass::collection($paginated);
        $responseData = $collection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $responseData['data'] ?? [],
            'meta' => $responseData['meta'] ?? null,
            'links' => $responseData['links'] ?? null,
        ]);
    }
}

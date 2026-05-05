<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Provides consistent pagination, search, sort, and filter for list endpoints.
 * Uses Spatie Query Builder for clean, standard query building.
 *
 * Usage in a controller:
 *   return $this->paginatedResponse(
 *       query: QueryBuilder::for(Course::class)
 *           ->with('category'),
 *       request: $request,
 *       resourceClass: CourseResource::class,
 *       allowedFilters: ['status', 'level', 'category_id'],
 *       allowedSorts: ['title', 'price', 'created_at'],
 *       allowedIncludes: ['category', 'teacher'],
 *   );
 */
trait HasPagination
{
    /**
     * @param  class-string<JsonResource>  $resourceClass
     * @param  array<string>  $searchColumns  Columns to search in (if search param provided)
     * @param  array<string>  $allowedFilters  Columns that may be filtered
     * @param  array<string>  $allowedSorts  Columns that may be sorted
     * @param  array<string>  $allowedIncludes  Relations that may be included
     */
    protected function paginatedResponse(
        QueryBuilder $query,
        Request $request,
        string $resourceClass,
        array $searchColumns = [],
        array $allowedFilters = [],
        array $allowedSorts = ['created_at'],
        array $allowedIncludes = [],
    ): JsonResponse {
        // Apply search if provided
        if ($request->filled('search') && ! empty($searchColumns)) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhereRaw("LOWER({$column}) LIKE ?", ['%'.mb_strtolower($search).'%']);
                }
            });
        }

        // Apply filters, sorts, and includes
        $query = $query
            ->allowedFilters(...$allowedFilters)
            ->allowedSorts(...$allowedSorts)
            ->allowedIncludes(...$allowedIncludes);

        // Get per_page with validation
        $perPage = $this->getPerPage($request);

        // Paginate
        $paginated = $query->paginate($perPage)->withQueryString();

        // Format response
        return $this->formatPaginatedResponse($paginated, $resourceClass);
    }

    /**
     * Get the per_page value from request, with validation.
     */
    private function getPerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 15);

        return max(1, min($perPage, 100));
    }

    /**
     * Format the paginated response.
     */
    private function formatPaginatedResponse($paginated, string $resourceClass): JsonResponse
    {
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

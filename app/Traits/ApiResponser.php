<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponser
{
    protected function success(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error(?string $message = null, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return paginated response with metadata
     */
    protected function successPaginated($resourceCollection, ?string $message = null, int $code = 200): JsonResponse
    {
        // If it's a resource collection with pagination
        if ($resourceCollection instanceof AnonymousResourceCollection) {
            $paginator = $resourceCollection->resource;
            
            if ($paginator instanceof LengthAwarePaginator) {
                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'data' => $resourceCollection->items(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'has_more_pages' => $paginator->hasMorePages(),
                        'next_page_url' => $paginator->nextPageUrl(),
                        'prev_page_url' => $paginator->previousPageUrl(),
                    ]
                ], $code);
            }
        }

        // Fallback to regular success response
        return $this->success($resourceCollection, $message, $code);
    }
}



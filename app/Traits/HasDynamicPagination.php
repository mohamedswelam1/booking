<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasDynamicPagination
{
    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = (int) $request->get('per_page', $this->getDefaultPerPage());
        $page = (int) $request->get('page', 1);

        // Validate per_page limits
        $perPage = max(1, min($perPage, $this->getMaxPerPage()));

        return [
            'per_page' => $perPage,
            'page' => $page,
        ];
    }

    /**
     * Get per page value from request with validation
     */
    protected function getPerPage(Request $request): int
    {
        return $this->getPaginationParams($request)['per_page'];
    }

    /**
     * Get default per page value
     */
    protected function getDefaultPerPage(): int
    {
        return config('app.pagination.default_per_page', 15);
    }

    /**
     * Get maximum allowed per page value
     */
    protected function getMaxPerPage(): int
    {
        return config('app.pagination.max_per_page', 100);
    }

    /**
     * Add pagination meta to response
     */
    protected function withPaginationMeta(array $data, $paginator): array
    {
        return array_merge($data, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ]);
    }
}

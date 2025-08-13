<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;

/**
 * Base API controller with common functionality
 */
abstract class BaseApiController extends Controller
{
    use ApiResponser;

    /**
     * Common success response with resource transformation
     */
    protected function successResource($resource, string $message = null, int $code = 200): JsonResponse
    {
        return $this->success($resource, $message, $code);
    }

    /**
     * Common pagination response
     */
    protected function successPaginated($resourceCollection, ?string $message = null, int $code = 200): JsonResponse
    {
        return $this->success($resourceCollection, $message, $code);
    }

    /**
     * Standard created response
     */
    protected function created($resource, string $message = 'Created'): JsonResponse
    {
        return $this->success($resource, $message, 201);
    }

    /**
     * Standard updated response
     */
    protected function updated($resource, string $message = 'Updated'): JsonResponse
    {
        return $this->success($resource, $message);
    }

    /**
     * Standard deleted response
     */
    protected function deleted(string $message = 'Deleted'): JsonResponse
    {
        return $this->success(null, $message);
    }

    /**
     * Standard not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Standard validation error response
     */
    protected function validationError(string $message = 'Validation failed', $errors = null): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Standard forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Handle service exceptions with standardized responses
     */
    protected function handleServiceException(\Exception $e): JsonResponse
    {
        if ($e instanceof \InvalidArgumentException) {
            return $this->validationError($e->getMessage());
        }

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->notFound();
        }

        // Log unexpected errors
        \Log::error('Service exception: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);

        return $this->error('An unexpected error occurred', 500);
    }
}

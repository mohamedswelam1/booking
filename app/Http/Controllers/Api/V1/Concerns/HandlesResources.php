<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Trait for standardized resource handling in controllers
 */
trait HandlesResources
{
    /**
     * Execute a service action and handle common exceptions
     */
    protected function executeServiceAction(callable $action): JsonResponse
    {
        try {
            $result = $action();
            return $this->handleServiceResult($result);
        } catch (\InvalidArgumentException $e) {
            return $this->validationError($e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->forbidden();
        } catch (\Exception $e) {
            return $this->handleServiceException($e);
        }
    }

    /**
     * Handle different types of service results
     */
    protected function handleServiceResult($result): JsonResponse
    {
        if (is_array($result)) {
            return $this->success($result);
        }

        if ($result instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return $this->successPaginated($result);
        }

        if ($result instanceof \Illuminate\Database\Eloquent\Model) {
            return $this->success($result);
        }

        return $this->success($result);
    }

    /**
     * Standard CRUD operations with consistent responses
     */
    protected function performIndex(callable $action): JsonResponse
    {
        return $this->executeServiceAction($action);
    }

    protected function performStore(callable $action): JsonResponse
    {
        try {
            $result = $action();
            return $this->created($result);
        } catch (\Exception $e) {
            return $this->handleServiceException($e);
        }
    }

    protected function performUpdate(callable $action): JsonResponse
    {
        try {
            $result = $action();
            return $this->updated($result);
        } catch (\Exception $e) {
            return $this->handleServiceException($e);
        }
    }

    protected function performDestroy(callable $action): JsonResponse
    {
        try {
            $action();
            return $this->deleted();
        } catch (\Exception $e) {
            return $this->handleServiceException($e);
        }
    }
}

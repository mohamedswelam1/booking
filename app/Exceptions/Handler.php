<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Handle unauthenticated user exceptions.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return $this->error('Unauthenticated', 401);
        }

        return redirect()->guest($exception->redirectTo($request) ?? route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return JSON responses.
     */
    private function handleApiException(Request $request, Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return $this->error('Validation failed', 422, $e->errors());
        }

        if ($e instanceof QueryException) {
            return $this->handleDatabaseException($e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->error('Forbidden', 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->error('Resource not found', 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->error('Endpoint not found', 404);
        }

        if ($e instanceof HttpException) {
            return $this->error($e->getMessage() ?: 'HTTP Error', $e->getStatusCode());
        }

        // Handle other exceptions in production
        if (app()->environment('production')) {
            return $this->error('Internal server error', 500);
        }

        // In development, show the actual error
        return $this->error($e->getMessage(), 500, [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    /**
     * Handle database exceptions with user-friendly messages.
     */
    private function handleDatabaseException(QueryException $e)
    {       

        // Default database error message
        if (app()->environment('production')) {
            return $this->error('A database error occurred. Please try again or contact support.', 422);
        }

        // In development, show more details
        return $this->error('Database error: ' . $e->getMessage(), 422);
    }
}

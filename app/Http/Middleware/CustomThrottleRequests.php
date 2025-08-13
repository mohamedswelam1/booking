<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottleRequests extends ThrottleRequests
{
    use ApiResponser;

    /**
     * Create a 'too many attempts' response.
     */
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return $this->buildResponse($request, $retryAfter, $headers);
    }

    /**
     * Build the rate limit exceeded response.
     */
    protected function buildResponse($request, $retryAfter, $headers)
    {
        if ($request->expectsJson()) {
            $message = 'Too many requests. Please slow down and try again.';
            
            // Customize message based on the rate limiter name
            $routeName = $request->route()?->getName();
            $action = $this->determineAction($request);
            
            if ($action) {
                $message = "Too many {$action} attempts. Please wait {$retryAfter} seconds before trying again.";
            }

            $response = $this->error($message, 429, [
                'retry_after_seconds' => $retryAfter,
                'retry_after_human' => $this->formatRetryAfter($retryAfter),
            ]);

            return $response->withHeaders($headers);
        }

        return response('Too Many Requests', 429, $headers);
    }

    /**
     * Determine the action being rate limited based on the request.
     */
    protected function determineAction(Request $request): ?string
    {
        $method = $request->method();
        $path = $request->path();

        if (str_contains($path, 'bookings')) {
            if ($method === 'POST' && !str_contains($path, '/confirm') && !str_contains($path, '/cancel')) {
                return 'booking creation';
            }
            if (str_contains($path, '/confirm')) {
                return 'booking confirmation';
            }
            if (str_contains($path, '/cancel')) {
                return 'booking cancellation';
            }
            if ($method === 'GET') {
                return 'booking retrieval';
            }
            return 'booking';
        }

        if (str_contains($path, 'availability')) {
            return 'availability check';
        }

        return null;
    }

    /**
     * Format retry after time in human-readable format.
     */
    protected function formatRetryAfter(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} second" . ($seconds === 1 ? '' : 's');
        }

        $minutes = round($seconds / 60);
        return "{$minutes} minute" . ($minutes === 1 ? '' : 's');
    }
}

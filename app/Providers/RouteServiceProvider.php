<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Rate limiting for booking creation (customers only)
        RateLimiter::for('bookings', function (Request $request) {
            if (!$request->user()) {
                // Stricter limit for guests/IP-based requests
                return Limit::perMinute(3)->by($request->ip());
            }
            
            if ($request->user()->role === 'customer') {
                // Per-user limits for authenticated customers
                return [
                    Limit::perMinute(5)->by($request->user()->id),
                    Limit::perDay(50)->by($request->user()->id),
                ];
            }
            
            // Default fallback
            return Limit::perMinute(10)->by($request->user()->id);
        });

        // Rate limiting for booking management actions (confirm/cancel)
        RateLimiter::for('booking-actions', function (Request $request) {
            if (!$request->user()) {
                return Limit::perMinute(1)->by($request->ip());
            }
            
            if ($request->user()->role === 'provider') {
                // More lenient for providers managing bookings
                return [
                    Limit::perMinute(20)->by($request->user()->id),
                    Limit::perDay(200)->by($request->user()->id),
                ];
            }
            
            if ($request->user()->role === 'customer') {
                // Standard limit for customers canceling their bookings
                return [
                    Limit::perMinute(10)->by($request->user()->id),
                    Limit::perDay(100)->by($request->user()->id),
                ];
            }
            
            // Default fallback
            return Limit::perMinute(5)->by($request->user()->id);
        });

        // Rate limiting for booking listing/viewing
        RateLimiter::for('booking-read', function (Request $request) {
            return [
                // Generous limits for reading booking data
                Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(1000)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Rate limiting for availability checking (public endpoint)
        RateLimiter::for('availability', function (Request $request) {
            return [
                // IP-based for public endpoint
                Limit::perMinute(30)->by($request->ip()),
                Limit::perHour(500)->by($request->ip()),
            ];
        });

        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                // Authenticated users get higher limits
                return Limit::perMinute(120)->by($request->user()->id);
            }
            
            // IP-based for unauthenticated requests
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}

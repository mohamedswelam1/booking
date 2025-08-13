<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\AvailabilityController;
use App\Http\Controllers\Api\V1\PublicServiceController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\AdminReportController;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Authentication (no additional rate limiting needed)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Provider Services
        Route::middleware('role:provider')->group(function () {
            Route::get('/provider/services', [ServiceController::class, 'index']);
            Route::post('/provider/services', [ServiceController::class, 'store']);
            Route::put('/provider/services/{service}', [ServiceController::class, 'update'])->middleware('can:update,service');
            Route::delete('/provider/services/{service}', [ServiceController::class, 'destroy'])->middleware('can:delete,service');

            // Availability
            Route::get('/provider/availability', [AvailabilityController::class, 'index']);
            Route::post('/provider/availability', [AvailabilityController::class, 'store']);
        });

        // Booking Management with specific rate limiting
        Route::get('/bookings', [BookingController::class, 'index'])
            ->middleware('throttle:booking-read');
        
        Route::post('/bookings', [BookingController::class, 'store'])
            ->middleware(['role:customer', 'throttle:bookings']);
        
        Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])
            ->middleware(['role:provider', 'can:update,booking', 'throttle:booking-actions']);
        
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->middleware(['can:update,booking', 'throttle:booking-actions']);

        // Admin reports
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/reports/bookings-summary', [AdminReportController::class, 'summary']);
            Route::get('/admin/reports/bookings-summary/export', [AdminReportController::class, 'exportSummary']);
            Route::get('/admin/reports/peak-hours', [AdminReportController::class, 'peakHours']);
        });
    });

    // Public service browsing with rate limiting
    Route::get('/services', [PublicServiceController::class, 'index']);
    Route::get('/services/{service}/availability', [PublicServiceController::class, 'slots'])
        ->middleware('throttle:availability');
    
    // Swagger documentation route
    Route::get('/docs', function () {
        return redirect('/api/documentation');
    });
});



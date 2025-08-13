<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Service Booking API",
 *     version="1.0.0",
 *     description="Production-Quality Service Booking RESTful API in Laravel 12. This API serves a multi-user platform for managing service provider availability, enabling real-time booking by customers, and providing administrators with powerful reporting tools.",
 *     @OA\Contact(
 *         email="admin@booking.api"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * @OA\Server(
 *     url="/",
 *     description="Current Domain"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     description="Enter token in format: Bearer {token}"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Services",
 *     description="Service management for providers"
 * )
 * @OA\Tag(
 *     name="Availability",
 *     description="Provider availability management"
 * )
 * @OA\Tag(
 *     name="Bookings",
 *     description="Booking management"
 * )
 * @OA\Tag(
 *     name="Public",
 *     description="Public endpoints for browsing services"
 * )
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin reporting endpoints"
 * )
 */
class SwaggerController extends Controller
{
    //
}

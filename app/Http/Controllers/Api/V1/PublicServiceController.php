<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AvailabilityContract;
use App\Contracts\ServiceContract;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;

class PublicServiceController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly AvailabilityContract $availability,
        private readonly ServiceContract $services
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     tags={"Public"},
     *     summary="Browse published services",
     *     description="Get all published services with optional filtering by category",
     *     @OA\Parameter(
     *         name="filter[category_id]",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="string", example="01hw987654321")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (1-100)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Services retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="01hw123456789"),
     *                         @OA\Property(property="name", type="string", example="Haircut"),
     *                         @OA\Property(property="description", type="string", example="Professional haircut service"),
     *                         @OA\Property(property="duration", type="integer", example=30),
     *                         @OA\Property(property="price", type="string", example="25.00"),
     *                         @OA\Property(property="is_published", type="boolean", example=true),
     *                         @OA\Property(property="category_id", type="string", example="01hw987654321"),
     *                         @OA\Property(property="provider_id", type="string", example="01hw555666777")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request) : JsonResponse
    {
        $services = $this->services->listPublished($request->all(), $request);
        return $this->successPaginated(ServiceResource::collection($services));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{service}/availability",
     *     tags={"Public"},
     *     summary="Get available time slots",
     *     description="Get dynamically calculated, bookable time slots for the next 7 days",
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Service ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01hw123456789")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available slots retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="2024-01-15", type="array",
     *                     @OA\Items(type="string", example="2024-01-15T10:00:00+00:00")
     *                 ),
     *                 @OA\Property(property="2024-01-16", type="array",
     *                     @OA\Items(type="string", example="2024-01-16T09:00:00+00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function slots(Request $request, Service $service) : JsonResponse
    {
        $provider = $service->provider;
        $days = $this->availability->nextDaysSlots($provider, $service, 7);
        return $this->success($days);
    }
}



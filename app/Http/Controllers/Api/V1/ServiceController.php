<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ServiceContract;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Http\Requests\Service\ServiceStoreRequest;
use App\Http\Requests\Service\ServiceUpdateRequest;
use App\Http\Resources\ServiceResource;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponser;

    public function __construct(private readonly ServiceContract $services)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/provider/services",
     *     tags={"Services"},
     *     summary="List provider's services",
     *     description="Get all services for the authenticated provider",
     *     security={{"sanctum":{}}},
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
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01hw123456789"),
     *                     @OA\Property(property="name", type="string", example="Haircut"),
     *                     @OA\Property(property="description", type="string", example="Professional haircut service"),
     *                     @OA\Property(property="duration", type="integer", example=30),
     *                     @OA\Property(property="price", type="string", example="25.00"),
     *                     @OA\Property(property="is_published", type="boolean", example=true),
     *                     @OA\Property(property="category_id", type="string", example="01hw987654321"),
     *                     @OA\Property(property="provider_id", type="string", example="01hw555666777")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15),
     *                 @OA\Property(property="has_more_pages", type="boolean", example=true),
     *                 @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/v1/provider/services?page=2"),
     *                 @OA\Property(property="prev_page_url", type="string", example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not a provider"
     *     )
     * )
     */
    public function index(Request $request) : JsonResponse
    {
        $provider = $request->user();
        $data = $this->services->providerIndex($provider, $request);
        return $this->successPaginated(ServiceResource::collection($data));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/provider/services",
     *     tags={"Services"},
     *     summary="Create a new service",
     *     description="Create a new service for the authenticated provider",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id","name","duration","price"},
     *             @OA\Property(property="category_id", type="string", example="01hw987654321"),
     *             @OA\Property(property="name", type="string", example="Haircut"),
     *             @OA\Property(property="description", type="string", example="Professional haircut service"),
     *             @OA\Property(property="duration", type="integer", example=30, description="Duration in minutes"),
     *             @OA\Property(property="price", type="number", format="float", example=25.00),
     *             @OA\Property(property="is_published", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Created"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01hw123456789"),
     *                 @OA\Property(property="name", type="string", example="Haircut"),
     *                 @OA\Property(property="description", type="string", example="Professional haircut service"),
     *                 @OA\Property(property="duration", type="integer", example=30),
     *                 @OA\Property(property="price", type="string", example="25.00"),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="category_id", type="string", example="01hw987654321"),
     *                 @OA\Property(property="provider_id", type="string", example="01hw555666777")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not a provider"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(ServiceStoreRequest $request) : JsonResponse
    {
        $provider = $request->user();
        $validated = $request->validated();

        $service = $this->services->createForProvider($provider, $validated);
        return $this->success(new ServiceResource($service), 'Created', 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/provider/services/{service}",
     *     tags={"Services"},
     *     summary="Update a service",
     *     description="Update an existing service (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Service ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01hw123456789")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category_id", type="string", example="01hw987654321"),
     *             @OA\Property(property="name", type="string", example="Premium Haircut"),
     *             @OA\Property(property="description", type="string", example="Premium haircut with styling"),
     *             @OA\Property(property="duration", type="integer", example=45),
     *             @OA\Property(property="price", type="number", format="float", example=35.00),
     *             @OA\Property(property="is_published", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function update(ServiceUpdateRequest $request, Service $service) : JsonResponse
    {
        $validated = $request->validated();
        $service = $this->services->update($service, $validated);
        return $this->success(new ServiceResource($service), 'Updated');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/provider/services/{service}",
     *     tags={"Services"},
     *     summary="Delete a service",
     *     description="Delete an existing service (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Service ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01hw123456789")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Deleted"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */
    public function destroy(Service $service) : JsonResponse
    {
        $this->services->delete($service);
        return $this->success(null, 'Deleted');
    }
}



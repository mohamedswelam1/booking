<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AvailabilityContract;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Availability\AvailabilityStoreRequest;
use App\Http\Resources\AvailabilityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    use ApiResponser;

    public function __construct(private readonly AvailabilityContract $availability)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/provider/availability",
     *     tags={"Availability"},
     *     summary="Get provider's availability schedule",
     *     description="Get the recurring weekly availability schedule for the authenticated provider",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Availability schedule retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="01hw123456789"),
     *                     @OA\Property(property="provider_id", type="string", example="01hw555666777"),
     *                     @OA\Property(property="day_of_week", type="integer", example=1, description="1=Monday, 7=Sunday"),
     *                     @OA\Property(property="start_time", type="string", example="09:00"),
     *                     @OA\Property(property="end_time", type="string", example="17:00"),
     *                     @OA\Property(property="timezone", type="string", example="UTC")
     *                 )
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
        $data = $this->availability->getRecurring($provider);
        return $this->success(AvailabilityResource::collection(collect($data)));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/provider/availability",
     *     tags={"Availability"},
     *     summary="Set provider's availability schedule",
     *     description="Create or update the recurring weekly availability schedule",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"entries"},
     *             @OA\Property(property="entries", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="day_of_week", type="integer", example=1, description="1=Monday, 7=Sunday"),
     *                     @OA\Property(property="start_time", type="string", example="09:00", description="HH:MM format"),
     *                     @OA\Property(property="end_time", type="string", example="17:00", description="HH:MM format"),
     *                     @OA\Property(property="timezone", type="string", example="UTC")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Availability schedule saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Saved"),
     *             @OA\Property(property="data", type="null")
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
    public function store(AvailabilityStoreRequest $request) : JsonResponse
    {
        $provider = $request->user();
        $validated = $request->validated();

        $this->availability->upsertRecurring($provider, $validated['entries']);
        return $this->success(null, 'Saved', 201);
    }
}



<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\BookingContract;
use App\Contracts\ServiceContract;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Traits\ApiResponser;
use App\Http\Requests\Booking\BookingStoreRequest;
use App\Http\Resources\BookingResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponser;

    public function __construct(
        private readonly BookingContract $bookings,
        private readonly ServiceContract $services,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="List user's bookings",
     *     description="Get bookings for the authenticated user (customer sees their bookings, provider sees bookings for their services)",
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
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="01hw987654321"),
     *                         @OA\Property(property="customer_id", type="string", example="01hw111222333"),
     *                         @OA\Property(property="provider_id", type="string", example="01hw444555666"),
     *                         @OA\Property(property="service_id", type="string", example="01hw123456789"),
     *                         @OA\Property(property="start_time", type="string", example="2024-01-15T10:00:00+00:00"),
     *                         @OA\Property(property="end_time", type="string", example="2024-01-15T10:30:00+00:00"),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="total_price", type="string", example="25.00")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request) : JsonResponse
    {
        $actor = $request->user();
        $data = method_exists($this->bookings, 'listForActor')
            ? $this->bookings->listForActor($actor, $request)
            : \App\Models\Booking::query()
                ->when($actor->role === 'provider', fn ($q) => $q->where('provider_id', $actor->id))
                ->when($actor->role === 'customer', fn ($q) => $q->where('customer_id', $actor->id))
                ->latest('start_time')
                ->paginate();
        return $this->successPaginated(BookingResource::collection($data));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="Create a new booking",
     *     description="Book a service for a specific time slot (customers only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_id","start_time"},
     *             @OA\Property(property="service_id", type="string", example="01hw123456789"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Created"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01hw987654321"),
     *                 @OA\Property(property="customer_id", type="string", example="01hw111222333"),
     *                 @OA\Property(property="provider_id", type="string", example="01hw444555666"),
     *                 @OA\Property(property="service_id", type="string", example="01hw123456789"),
     *                 @OA\Property(property="start_time", type="string", example="2024-01-15T10:00:00+00:00"),
     *                 @OA\Property(property="end_time", type="string", example="2024-01-15T10:30:00+00:00"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_price", type="string", example="25.00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not a customer"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Time slot already occupied or validation error"
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests - rate limit exceeded"
     *     )
     * )
     */
    public function store(BookingStoreRequest $request) : JsonResponse
    {
        $validated = $request->validated();

        $service = $this->services->findById($validated['service_id']);
        $start = CarbonImmutable::parse($validated['start_time'])->utc();
        try {
            $booking = $this->bookings->createBooking($request->user(), $service, $start);
            return $this->success(new BookingResource($booking), 'Created', 201);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings/{booking}/confirm",
     *     tags={"Bookings"},
     *     summary="Confirm a booking",
     *     description="Confirm a pending booking (provider only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01hw987654321")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking confirmed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Confirmed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01hw987654321"),
     *                 @OA\Property(property="status", type="string", example="confirmed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the provider"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function confirm(Booking $booking) : JsonResponse
    {
        $this->bookings->confirmBooking($booking);
        return $this->success($booking->refresh(), 'Confirmed');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings/{booking}/cancel",
     *     tags={"Bookings"},
     *     summary="Cancel a booking",
     *     description="Cancel a booking (provider or customer)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="string", example="01hw987654321")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cancelled"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="01hw987654321"),
     *                 @OA\Property(property="status", type="string", example="cancelled")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not authorized to cancel this booking"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function cancel(Request $request, Booking $booking) : JsonResponse       
    {
        $this->bookings->cancelBooking($booking, $request->user());
        return $this->success($booking->refresh(), 'Cancelled');
    }
}



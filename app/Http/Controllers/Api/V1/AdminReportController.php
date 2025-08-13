<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Contracts\AdminReportContract;
use App\Exports\BookingsSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Admin\AdminReportRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminReportController extends Controller
{
    use ApiResponser;

    public function __construct(private readonly AdminReportContract $reports)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/reports/bookings-summary",
     *     tags={"Admin"},
     *     summary="Get bookings summary report",
     *     description="Get total bookings, cancellation rates, etc. with optional filtering",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="provider_id",
     *         in="query",
     *         description="Filter by provider ID",
     *         required=false,
     *         @OA\Schema(type="string", example="01hw555666777")
     *     ),
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="Filter by service ID",
     *         required=false,
     *         @OA\Schema(type="string", example="01hw123456789")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Summary report retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="confirmed", type="integer", example=120),
     *                 @OA\Property(property="cancelled", type="integer", example=20),
     *                 @OA\Property(property="completed", type="integer", example=100),
     *                 @OA\Property(property="cancellation_rate", type="number", format="float", example=0.133)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not an admin"
     *     )
     * )
     */
    public function summary(AdminReportRequest $request) : JsonResponse
    {
        $validated = $request->validated();

        return $this->success($this->reports->bookingsSummary($validated));
    }

    public function peakHours(AdminReportRequest $request) : JsonResponse
    {
        $validated = $request->validated();
        return $this->success($this->reports->peakHours($validated));
    }

    public function exportSummary(AdminReportRequest $request) : BinaryFileResponse
    {
        $validated = $request->validated();
        
        return Excel::download(
            new BookingsSummaryExport($this->reports, $validated),
            'bookings-summary-' . now()->format('Y-m-d-H-i-s') . '.csv'
        );
    }
}



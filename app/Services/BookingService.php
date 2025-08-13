<?php

namespace App\Services;

use App\Contracts\BookingContract;
use App\Contracts\Repositories\BookingRepositoryContract;
use App\Models\Booking;
use App\Models\Service as ServiceModel;
use App\Models\User;
use App\Traits\HasDynamicPagination;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookingService implements BookingContract
{
    use HasDynamicPagination;

    public function __construct(
        private readonly BookingRepositoryContract $bookings
    ) {
    }

    public function listForActor(User $actor, ?Request $request = null): LengthAwarePaginator
    {
        $perPage = $request ? $this->getPerPage($request) : $this->getDefaultPerPage();
        
        $query = \App\Models\Booking::query();
        if ($actor->role === 'provider') {
            $query->where('provider_id', $actor->id);
        } elseif ($actor->role === 'customer') {
            $query->where('customer_id', $actor->id);
        }
        return $query->latest('start_time')->paginate($perPage);
    }

    public function createBooking(User $customer, ServiceModel $service, CarbonInterface $startTime): Booking
    {
        if ($customer->role !== 'customer') {
            throw new InvalidArgumentException('Only customers can create bookings.');
        }

        $endTime = $startTime->copy()->addMinutes($service->duration);

        return DB::transaction(function () use ($customer, $service, $startTime, $endTime) {
            if ($this->bookings->existsConfirmedOverlap($service->provider, $startTime, $endTime)) {
                throw new InvalidArgumentException('Time slot already occupied.');
            }

            $booking = $this->bookings->create([
                'customer_id' => $customer->id,
                'provider_id' => $service->provider_id,
                'service_id' => $service->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'status' => 'pending',
                'total_price' => $service->price,
            ]);

            // Dispatch event for notifications
            \App\Events\BookingCreated::dispatch($booking);

            return $booking;
        });
    }

    public function confirmBooking(Booking $booking): Booking
    {
        $booking->status = 'confirmed';
        $booking->save();
        return $booking;
    }

    public function cancelBooking(Booking $booking, User $actor): Booking
    {
        $booking->status = 'cancelled';
        $booking->save();
        return $booking;
    }
}



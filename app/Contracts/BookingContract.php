<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface BookingContract
{
    public function createBooking(User $customer, Service $service, CarbonInterface $startTime): Booking;
    public function confirmBooking(Booking $booking): Booking;
    public function cancelBooking(Booking $booking, User $actor): Booking;
    public function listForActor(User $actor, ?Request $request = null): LengthAwarePaginator;
}



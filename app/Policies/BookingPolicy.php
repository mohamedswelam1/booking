<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function update(User $user, Booking $booking): bool
    {
        return $user->id === $booking->provider_id || $user->id === $booking->customer_id;
    }
}



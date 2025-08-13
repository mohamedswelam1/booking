<?php

namespace App\Repositories;

use App\Contracts\Repositories\BookingRepositoryContract;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonInterface;

class BookingRepository implements BookingRepositoryContract
{
    public function create(array $attributes): Booking
    {
        return Booking::create($attributes);
    }

    public function existsConfirmedOverlap(User $provider, CarbonInterface $start, CarbonInterface $end): bool
    {
        return Booking::query()
            ->where('provider_id', $provider->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            })->exists();
    }

    public function forActor(User $actor)
    {
        $query = Booking::query();
        if ($actor->role === 'provider') {
            $query->where('provider_id', $actor->id);
        } elseif ($actor->role === 'customer') {
            $query->where('customer_id', $actor->id);
        }
        return $query;
    }
}



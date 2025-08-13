<?php

namespace App\Contracts\Repositories;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;

interface BookingRepositoryContract
{
    public function create(array $attributes): Booking;
    public function existsConfirmedOverlap(User $provider, CarbonInterface $start, CarbonInterface $end): bool;
    public function forActor(User $actor);
}



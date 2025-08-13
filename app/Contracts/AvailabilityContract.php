<?php

namespace App\Contracts;

use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;

interface AvailabilityContract
{
    /**
     * Return available start times for the given service and day, considering duration and existing bookings.
     * Returned datetimes must be in UTC.
     *
     * @return list<CarbonInterface>
     */
    public function getAvailableSlots(User $provider, Service $service, CarbonInterface $day): array;
    /**
     * Returns an associative array of date => list of ISO8601 slot strings for next N days.
     *
     * @return array<string, list<string>>
     */
    public function nextDaysSlots(User $provider, Service $service, int $days = 7): array;

    /** @return list<\App\Models\Availability> */
    public function getRecurring(User $provider): array;
    public function upsertRecurring(User $provider, array $entries): void;
}



<?php

namespace App\Contracts\Repositories;

use App\Models\Availability;
use App\Models\AvailabilityOverride;
use App\Models\User;
use Carbon\CarbonInterface;

interface AvailabilityRepositoryContract
{
    public function upsertRecurring(User $provider, array $entries): void;
    /** @return list<Availability> */
    public function getRecurringForProvider(User $provider): array;
    /** @return list<AvailabilityOverride> */
    public function getOverrides(User $provider, CarbonInterface $from, CarbonInterface $to): array;
}



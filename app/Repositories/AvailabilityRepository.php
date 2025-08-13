<?php

namespace App\Repositories;

use App\Contracts\Repositories\AvailabilityRepositoryContract;
use App\Models\Availability;
use App\Models\AvailabilityOverride;
use App\Models\User;
use Carbon\CarbonInterface;

class AvailabilityRepository implements AvailabilityRepositoryContract
{
    public function upsertRecurring(User $provider, array $entries): void
    {
        foreach ($entries as $entry) {
            Availability::updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'day_of_week' => (int) $entry['day_of_week'],
                    'start_time' => $entry['start_time'],
                ],
                [
                    'end_time' => $entry['end_time'],
                    'timezone' => $entry['timezone'],
                ]
            );
        }
    }

    public function getRecurringForProvider(User $provider): array
    {
        return Availability::query()
            ->where('provider_id', $provider->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->all();
    }

    public function getOverrides(User $provider, CarbonInterface $from, CarbonInterface $to): array
    {
        return AvailabilityOverride::query()
            ->where('provider_id', $provider->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get()
            ->all();
    }
}



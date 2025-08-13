<?php

namespace App\Services;

use App\Contracts\AdminReportContract;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class AdminReportService implements AdminReportContract
{
    public function bookingsSummary(array $filters): array
    {
        $q = Booking::query();
        if (!empty($filters['provider_id'])) {
            $q->where('provider_id', $filters['provider_id']);
        }
        if (!empty($filters['service_id'])) {
            $q->where('service_id', $filters['service_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->where('start_time', '>=', CarbonImmutable::parse($filters['date_from'])->startOfDay());
        }
        if (!empty($filters['date_to'])) {
            $q->where('start_time', '<=', CarbonImmutable::parse($filters['date_to'])->endOfDay());
        }

        $total = (clone $q)->count();
        $confirmed = (clone $q)->where('status', 'confirmed')->count();
        $cancelled = (clone $q)->where('status', 'cancelled')->count();
        $completed = (clone $q)->where('status', 'completed')->count();

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'cancelled' => $cancelled,
            'completed' => $completed,
            'cancellation_rate' => $total > 0 ? round($cancelled / $total, 3) : 0.0,
        ];
    }

    public function peakHours(array $filters): array
    {
        $q = Booking::query()->whereIn('status', ['confirmed', 'completed']);
        if (!empty($filters['provider_id'])) {
            $q->where('provider_id', $filters['provider_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->where('start_time', '>=', CarbonImmutable::parse($filters['date_from'])->startOfDay());
        }
        if (!empty($filters['date_to'])) {
            $q->where('start_time', '<=', CarbonImmutable::parse($filters['date_to'])->endOfDay());
        }

        $buckets = [];
        foreach ($q->get(['start_time']) as $booking) {
            $start = CarbonImmutable::parse($booking->start_time);
            $key = $start->isoWeekday().'-'.$start->hour;
            $buckets[$key] = ($buckets[$key] ?? 0) + 1;
        }
        return $buckets;
    }
}



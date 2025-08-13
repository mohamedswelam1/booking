<?php

namespace App\Contracts;

interface AdminReportContract
{
    public function bookingsSummary(array $filters): array;
    public function peakHours(array $filters): array;
}



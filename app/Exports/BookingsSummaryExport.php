<?php

namespace App\Exports;

use App\Contracts\AdminReportContract;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingsSummaryExport implements FromArray, WithHeadings
{
    public function __construct(
        private readonly AdminReportContract $reports,
        private readonly array $filters
    ) {
    }

    public function array(): array
    {
        $summary = $this->reports->bookingsSummary($this->filters);
        
        return [
            [
                'total' => $summary['total'],
                'confirmed' => $summary['confirmed'],
                'cancelled' => $summary['cancelled'],
                'completed' => $summary['completed'],
                'cancellation_rate' => $summary['cancellation_rate'],
                'exported_at' => now()->toIso8601String(),
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Total Bookings',
            'Confirmed',
            'Cancelled', 
            'Completed',
            'Cancellation Rate',
            'Exported At',
        ];
    }
}

<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendProviderNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Booking $booking)
    {
    }

    public function handle(): void
    {
        // In a real app, this would send email/SMS/push notification
        // For now, just log the notification
        Log::info('Provider notification sent', [
            'booking_id' => $this->booking->id,
            'provider_id' => $this->booking->provider_id,
            'customer_name' => $this->booking->customer->name,
            'service_name' => $this->booking->service->name,
            'start_time' => $this->booking->start_time,
        ]);
    }
}

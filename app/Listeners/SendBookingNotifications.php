<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendProviderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        
        // Queue job to notify provider
        SendProviderNotification::dispatch($booking);
        
        // Could also queue customer confirmation email here
        // SendCustomerConfirmation::dispatch($booking);
    }
}

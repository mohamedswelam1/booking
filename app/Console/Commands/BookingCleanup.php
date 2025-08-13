<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class BookingCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending bookings older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = CarbonImmutable::now()->subHours(24);
        
        $cancelledCount = Booking::query()
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->update(['status' => 'cancelled']);

        $this->info("Cancelled {$cancelledCount} pending bookings older than 24 hours.");
        
        return self::SUCCESS;
    }
}

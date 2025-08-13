<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = CarbonImmutable::now()->addDays(2)->setTime(9, 0);
        return [
            'customer_id' => User::factory(),
            'provider_id' => User::factory(),
            'service_id' => Service::factory(),
            'start_time' => $start,
            'end_time' => $start->addMinutes(60),
            'status' => 'pending',
            'total_price' => 100.00,
        ];
    }
}



<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::where('is_published', true)->with('provider')->get();

        if ($customers->isEmpty() || $services->isEmpty()) {
            return;
        }

        // Create bookings for the past, present, and future
        $this->createPastBookings($customers, $services);
        $this->createCurrentBookings($customers, $services);
        $this->createFutureBookings($customers, $services);
    }

    private function createPastBookings($customers, $services): void
    {
        // Create 20 past bookings (last 30 days)
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers->random();
            $service = $services->random();
            
            $startTime = Carbon::now()
                ->subDays(rand(1, 30))
                ->setHour(rand(9, 16))
                ->setMinute(rand(0, 3) * 15) // 15-minute intervals
                ->setSecond(0);

            $endTime = $startTime->copy()->addMinutes($service->duration);

            $statuses = ['completed', 'completed', 'completed', 'cancelled']; // 75% completed, 25% cancelled
            $status = $statuses[array_rand($statuses)];

            Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $service->provider_id,
                'service_id' => $service->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'total_price' => $service->price,
            ]);
        }
    }

    private function createCurrentBookings($customers, $services): void
    {
        // Create 10 current bookings (today and tomorrow)
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $service = $services->random();
            
            $startTime = Carbon::now()
                ->addDays(rand(0, 1)) // Today or tomorrow
                ->setHour(rand(9, 16))
                ->setMinute(rand(0, 3) * 15) // 15-minute intervals
                ->setSecond(0);

            $endTime = $startTime->copy()->addMinutes($service->duration);

            $statuses = ['pending', 'confirmed', 'confirmed']; // Mix of pending and confirmed
            $status = $statuses[array_rand($statuses)];

            Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $service->provider_id,
                'service_id' => $service->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'total_price' => $service->price,
            ]);
        }
    }

    private function createFutureBookings($customers, $services): void
    {
        // Create 15 future bookings (next 30 days)
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $service = $services->random();
            
            $startTime = Carbon::now()
                ->addDays(rand(2, 30))
                ->setHour(rand(9, 16))
                ->setMinute(rand(0, 3) * 15) // 15-minute intervals
                ->setSecond(0);

            $endTime = $startTime->copy()->addMinutes($service->duration);

            $statuses = ['pending', 'confirmed', 'confirmed', 'confirmed']; // Mostly confirmed
            $status = $statuses[array_rand($statuses)];

            Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $service->provider_id,
                'service_id' => $service->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'total_price' => $service->price,
            ]);
        }

        // Create some test bookings for specific scenarios
        $this->createTestScenarios($customers, $services);
    }

    private function createTestScenarios($customers, $services): void
    {
        $testCustomer = User::where('email', 'test.customer@example.com')->first();
        $testProvider = User::where('email', 'test.provider@example.com')->first();
        
        if (!$testCustomer || !$testProvider) {
            return;
        }

        $testService = Service::where('provider_id', $testProvider->id)->first();
        
        if (!$testService) {
            return;
        }

        // Pending booking for testing confirmation
        Booking::create([
            'customer_id' => $testCustomer->id,
            'provider_id' => $testProvider->id,
            'service_id' => $testService->id,
            'start_time' => Carbon::now()->addDays(3)->setHour(10)->setMinute(0)->setSecond(0),
            'end_time' => Carbon::now()->addDays(3)->setHour(10)->addMinutes($testService->duration)->setSecond(0),
            'status' => 'pending',
            'total_price' => $testService->price,
        ]);

        // Confirmed booking for testing cancellation
        Booking::create([
            'customer_id' => $testCustomer->id,
            'provider_id' => $testProvider->id,
            'service_id' => $testService->id,
            'start_time' => Carbon::now()->addDays(5)->setHour(14)->setMinute(0)->setSecond(0),
            'end_time' => Carbon::now()->addDays(5)->setHour(14)->addMinutes($testService->duration)->setSecond(0),
            'status' => 'confirmed',
            'total_price' => $testService->price,
        ]);

        // Old pending booking for cleanup testing (older than 24 hours)
        Booking::create([
            'customer_id' => $testCustomer->id,
            'provider_id' => $testProvider->id,
            'service_id' => $testService->id,
            'start_time' => Carbon::now()->subDays(2)->setHour(15)->setMinute(0)->setSecond(0),
            'end_time' => Carbon::now()->subDays(2)->setHour(15)->addMinutes($testService->duration)->setSecond(0),
            'status' => 'pending',
            'total_price' => $testService->price,
            'created_at' => Carbon::now()->subDays(3), // Created 3 days ago
        ]);
    }
}
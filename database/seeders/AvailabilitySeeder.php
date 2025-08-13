<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\AvailabilityOverride;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = User::where('role', 'provider')->get();

        foreach ($providers as $provider) {
            // Create weekly availability schedules
            $this->createWeeklySchedule($provider);
            
            // Create some availability overrides (holidays, special hours, etc.)
            $this->createAvailabilityOverrides($provider);
        }
    }

    private function createWeeklySchedule(User $provider): void
    {
        // Different schedule patterns for variety
        $schedulePatterns = [
            // Full-time (Monday to Friday)
            [
                [1, '09:00:00', '17:00:00'], // Monday
                [2, '09:00:00', '17:00:00'], // Tuesday
                [3, '09:00:00', '17:00:00'], // Wednesday
                [4, '09:00:00', '17:00:00'], // Thursday
                [5, '09:00:00', '17:00:00'], // Friday
            ],
            // Extended hours with weekend
            [
                [1, '08:00:00', '18:00:00'], // Monday
                [2, '08:00:00', '18:00:00'], // Tuesday
                [3, '08:00:00', '18:00:00'], // Wednesday
                [4, '08:00:00', '18:00:00'], // Thursday
                [5, '08:00:00', '18:00:00'], // Friday
                [6, '10:00:00', '16:00:00'], // Saturday
            ],
            // Part-time with split shifts
            [
                [1, '09:00:00', '13:00:00'], // Monday morning
                [1, '14:00:00', '18:00:00'], // Monday afternoon
                [3, '09:00:00', '13:00:00'], // Wednesday morning
                [3, '14:00:00', '18:00:00'], // Wednesday afternoon
                [5, '09:00:00', '17:00:00'], // Friday
                [6, '09:00:00', '15:00:00'], // Saturday
            ],
            // Flexible hours
            [
                [2, '10:00:00', '19:00:00'], // Tuesday
                [3, '10:00:00', '19:00:00'], // Wednesday
                [4, '10:00:00', '19:00:00'], // Thursday
                [5, '08:00:00', '16:00:00'], // Friday
                [6, '09:00:00', '17:00:00'], // Saturday
                [7, '11:00:00', '15:00:00'], // Sunday
            ],
            // Evening availability
            [
                [1, '15:00:00', '21:00:00'], // Monday
                [2, '15:00:00', '21:00:00'], // Tuesday
                [3, '15:00:00', '21:00:00'], // Wednesday
                [4, '15:00:00', '21:00:00'], // Thursday
                [5, '15:00:00', '21:00:00'], // Friday
            ],
        ];

        $selectedPattern = $schedulePatterns[array_rand($schedulePatterns)];

        foreach ($selectedPattern as $schedule) {
            try {
                Availability::create([
                    'provider_id' => $provider->id,
                    'day_of_week' => $schedule[0],
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'timezone' => 'UTC',
                ]);
            } catch (\Exception $e) {
                // Skip if duplicate (same provider, day, start_time combination)
                continue;
            }
        }
    }

    private function createAvailabilityOverrides(User $provider): void
    {
        // Create overrides for the next 30 days
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        // Holiday break (unavailable)
        $holidayStart = Carbon::now()->addDays(rand(10, 20));
        AvailabilityOverride::create([
            'provider_id' => $provider->id,
            'date' => $holidayStart->format('Y-m-d'),
            'start_time' => '00:00:00',
            'end_time' => '23:59:59',
            'is_available' => false,
        ]);

        // Extended hours for a special day (available)
        $specialDay = Carbon::now()->addDays(rand(5, 15));
        AvailabilityOverride::create([
            'provider_id' => $provider->id,
            'date' => $specialDay->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time' => '09:00:00',
            'is_available' => true,
        ]);

        // Lunch break override (unavailable)
        $lunchBreakDay = Carbon::now()->addDays(rand(1, 7));
        AvailabilityOverride::create([
            'provider_id' => $provider->id,
            'date' => $lunchBreakDay->format('Y-m-d'),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'is_available' => false,
        ]);

        // Emergency availability on normally closed day
        $emergencyDay = Carbon::now()->addDays(rand(8, 25));
        if ($emergencyDay->dayOfWeek == 0) { // Sunday
            AvailabilityOverride::create([
                'provider_id' => $provider->id,
                'date' => $emergencyDay->format('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);
        }
    }
}
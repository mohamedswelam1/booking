<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\BookingRepositoryContract;
use App\Contracts\Repositories\ServiceRepositoryContract;
use App\Contracts\Repositories\AvailabilityRepositoryContract;
use App\Contracts\BookingContract;
use App\Contracts\AvailabilityContract;
use App\Repositories\BookingRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\AvailabilityRepository;
use App\Services\BookingService;
use App\Services\AvailabilityService;
use App\Contracts\ServiceContract;
use App\Services\ServiceService;
use App\Contracts\AdminReportContract;
use App\Services\AdminReportService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BookingRepositoryContract::class, BookingRepository::class);
        $this->app->bind(ServiceRepositoryContract::class, ServiceRepository::class);
        $this->app->bind(AvailabilityRepositoryContract::class, AvailabilityRepository::class);
        $this->app->bind(BookingContract::class, BookingService::class);
        $this->app->bind(AvailabilityContract::class, AvailabilityService::class);
        $this->app->bind(ServiceContract::class, ServiceService::class);
        $this->app->bind(AdminReportContract::class, AdminReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

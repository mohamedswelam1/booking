<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:cleanup')
    ->daily()
    ->description('Cancel pending bookings older than 24 hours');
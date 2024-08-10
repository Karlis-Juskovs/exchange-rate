<?php

use App\Jobs\DispatchImportExchangeRatesJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DispatchImportExchangeRatesJob())
    ->dailyAt('3:00');

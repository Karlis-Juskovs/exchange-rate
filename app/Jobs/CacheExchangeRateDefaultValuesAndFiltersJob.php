<?php

namespace App\Jobs;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CacheExchangeRateDefaultValuesAndFiltersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::forget('exchange_rate_default_values');
        Cache::forget('exchange_rate_filters');

        Cache::remember('exchange_rate_default_values', 86400, function () {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subWeek();

            $collection = ExchangeRate::whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('currency_abbreviation')
                ->orderBy('created_at')
                ->get();

            $dateArray = $collection->pluck('created_at')->all();
            $dateArray = array_unique($dateArray);

            return [$collection->groupBy('currency_abbreviation'), $dateArray];
        });

        Cache::remember('exchange_rate_filters', 86400, function () {
            $currencies = ExchangeRate::select('currency_abbreviation')
                ->distinct()
                ->pluck('currency_abbreviation')
                ->toArray();

            $minDate = ExchangeRate::min('created_at');
            $maxDate = ExchangeRate::max('created_at');

            return [$currencies, $minDate, $maxDate];
        });
    }
}

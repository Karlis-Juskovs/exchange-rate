<?php

namespace App\Jobs;

use App\Models\ExchangeRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchImportExchangeRatesJob implements ShouldQueue
{
    use Queueable;
    private Carbon $currentDate;
    private int $dayCount;

    /**
     * Create a new job instance.
     */
    public function __construct(int $dayCount = 7)
    {
        $this->currentDate = Carbon::now();
        $this->dayCount = $dayCount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /* First adds to Horizon queue Job to import today's Exchange rates, then checks last 6 days to see if data is
        missing in one of those days. Only if no records exist within that day, Job is added to queue for that specific
        day. Job utilizes transactions, so if there is at least one record in a given day, full correct set should also
        exist (it was either fully added or not added at all) */

        ImportExchangeRatesJob::dispatch($this->currentDate);

        for ($i = 1; $i < $this->dayCount; $i++) {
            $date = $this->currentDate->subDay();
            $exchangeRates = ExchangeRate::where('created_at', '=', $date)->get();

            //todo need to check if this if statement works
            if ($exchangeRates->count() === 0) {
                ImportExchangeRatesJob::dispatch($date);
            }
        }

        CacheExchangeRateDefaultValuesAndFiltersJob::dispatch()
            ->delay(now()->addMinutes(5));
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        /* Failed Jobs will also appear in Horizon panel */
        if ($admin = User::find(config('app.admin_id'))) {
            // email service is needed
            // then $admin->notify(new ...); can be used
        }

        CacheExchangeRateDefaultValuesAndFiltersJob::dispatch()
            ->delay(now()->addMinutes(5));
    }
}

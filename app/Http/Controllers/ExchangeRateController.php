<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchImportExchangeRatesJob;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_date && $request->end_date) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = Carbon::parse($request->end_date);
                    if ($startDate > $endDate) {
                        $fail('Start date must be before end date');
                    } else if ($startDate->diffInDays($endDate) > 7) {
                        $fail('The difference between start date and end date must not exceed 7 days.');
                    }
                }
            }],
            'currencies' => 'nullable|array',
            'currencies.*' => 'string',
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        [$exchangeRates, $dateArray] = ExchangeRate::getOrderedFilteredExchangeRates(
            $validated['currencies'] ?? [],
            $startDate ? Carbon::parse($startDate) : null,
            $endDate ? Carbon::parse($endDate) : null
        );
        [$currencies, $minDate, $maxDate] = Cache::get('exchange_rate_filters');

        return view('exchange_rate.index', [
            'exchangeRates' => $exchangeRates,
            'dateArray' => $dateArray,
            'currencies' => $currencies,
            'selectedCurrencies' => $validated['currencies'] ?? [],
            'minDate' => $minDate,
            'start_date' => $startDate,
            'maxDate' => $maxDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Function should be called using Tinker, to fill exchange_rates tables with exchange data from last 180 days
     */
    public function fillExchangeRatesTableWithExtraDataScript(): void
    {
        DispatchImportExchangeRatesJob::dispatch(dayCount:180);
    }
}

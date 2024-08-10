<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchImportExchangeRatesJob;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    public function index(Request $request): View
    {
        [$exchangeRates, $dateArray] = ExchangeRate::getOrderedFilteredExchangeRates($request);

        return view('exchange_rate.index', compact('exchangeRates', 'dateArray'));
    }

    /**
     * Function should be called using Tinker, to fill exchange_rates tables with exchange data from last 180 days
     */
    public function fillExchangeRatesTableWithExtraDataScript(): void
    {
        DispatchImportExchangeRatesJob::dispatch(dayCount:180);
    }
}

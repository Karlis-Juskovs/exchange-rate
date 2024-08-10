<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExchangeRate extends Model
{
    use HasFactory;

    public $timestamps = false;

    //------------------------------------------------------------------------------------------------------------------
    // Custom functions
    //------------------------------------------------------------------------------------------------------------------
    public static function getOrderedFilteredExchangeRates(
        ?array $currencyAbbreviations,
        ?string $startDate,
        ?string $endDate
    ): ?array
    {
        $builder = self::query();

        if ($currencyAbbreviations && count($currencyAbbreviations) > 0) {
            $builder->whereIn('currency_abbreviation', $currencyAbbreviations);
        }

        if (!$startDate || !$endDate) {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subWeek();
        }
        $builder->whereBetween('created_at', [$startDate, $endDate]);

        if (($currencyAbbreviations && count($currencyAbbreviations) > 0) || ($startDate && $endDate)) {
            $collection = $builder->orderBy('currency_abbreviation')
                ->orderBy('created_at')
                ->get();
        } else {
            return Cache::get('exchange_rate_default_values');
        }

        $dateArray = $collection->pluck('created_at')->all();
        $dateArray = array_unique($dateArray);

        return [$collection->groupBy('currency_abbreviation'), $dateArray];
    }
}

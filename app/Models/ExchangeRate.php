<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ExchangeRate extends Model
{
    use HasFactory;

    public $timestamps = false;

    //------------------------------------------------------------------------------------------------------------------
    // Custom functions
    //------------------------------------------------------------------------------------------------------------------
    public static function getOrderedFilteredExchangeRates(Request $request): array
    {
        $currencyAbbreviations = [];
        $startDate = '';
        $endDate = '';

        $builder = self::query();

        if (count($currencyAbbreviations) > 0) {
            $builder->whereIn('currency_abbreviation', $currencyAbbreviations);
        }

        if (!$startDate || !$endDate) {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subWeek();
        }
        $builder->whereBetween('created_at', [$startDate, $endDate]);

        $collection = $builder->orderBy('currency_abbreviation')
            ->orderBy('created_at')
            ->get();

        $dateArray = $collection->pluck('created_at')->all();
        $dateArray = array_unique($dateArray);

        return [$collection->groupBy('currency_abbreviation'), $dateArray];
    }
}

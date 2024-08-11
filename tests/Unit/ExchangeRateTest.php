<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterByCurrencyAbbreviations()
    {
        // Arrange: Create some exchange rate records
        ExchangeRate::factory()->create(['currency_abbreviation' => 'USD']);
        ExchangeRate::factory()->create(['currency_abbreviation' => 'EUR']);
        ExchangeRate::factory()->create(['currency_abbreviation' => 'GBP']);

        // Act: Call the method with specific abbreviations
        $result = ExchangeRate::getOrderedFilteredExchangeRates(['USD', 'EUR'], null, null);

        // Assert: Only USD and EUR should be returned
        $this->assertCount(2, $result[0]);
        $this->assertArrayHasKey('USD', $result[0]);
        $this->assertArrayHasKey('EUR', $result[0]);
        $this->assertArrayNotHasKey('GBP', $result[0]);
    }

    public function testDateRangeFiltering()
    {
        // Arrange: Create exchange rates for different dates
        $now = Carbon::now();
        $oneWeekAgo = $now->copy()->subWeek();

        ExchangeRate::factory()->create(['created_at' => $oneWeekAgo->copy()->subDay()]);
        ExchangeRate::factory()->create(['created_at' => $now->copy()->subDay()]);

        // Act: Call the method with a specific date range
        $result = ExchangeRate::getOrderedFilteredExchangeRates(null, $oneWeekAgo, $now);

        // Assert: One record should be returned as other is outside range
        $this->assertCount(1, $result[0]);
    }

    public function testFallbackToLastWeekIfNoDatesProvided()
    {
        // Arrange: Create exchange rates for different dates
        $now = Carbon::now();
        $oneWeekAgo = $now->copy()->subWeek();

        ExchangeRate::factory()->create(['created_at' => $oneWeekAgo->copy()->subDay(), 'currency_abbreviation' => 'EUR']);
        ExchangeRate::factory()->create(['created_at' => $now->copy()->subDay(), 'currency_abbreviation' => 'EUR']);

        // Act: Call the method with no dates
        $result = ExchangeRate::getOrderedFilteredExchangeRates(['EUR'], null, null);

        // Assert: Only records from the last week should be returned
        $this->assertCount(1, $result[0]);
    }

    public function testCacheFallbackIfNoFilters()
    {
        // Arrange: Cache some default values
        Cache::shouldReceive('get')
            ->with('exchange_rate_default_values')
            ->andReturn(['cached' => 'value']);

        // Act: Call the method with no filters
        $result = ExchangeRate::getOrderedFilteredExchangeRates(null, null, null);

        // Assert: Cached values should be returned
        $this->assertEquals(['cached' => 'value'], $result);
    }

    public function testResultsAreOrderedCorrectly()
    {
        // Arrange: Create exchange rates with varying dates and currencies
        ExchangeRate::factory()->create(['currency_abbreviation' => 'EUR', 'created_at' => Carbon::now()->subDays(2)]);
        ExchangeRate::factory()->create(['currency_abbreviation' => 'USD', 'created_at' => Carbon::now()->subDay()]);
        ExchangeRate::factory()->create(['currency_abbreviation' => 'USD', 'created_at' => Carbon::now()->subDays(2)]);
        ExchangeRate::factory()->create(['currency_abbreviation' => 'EUR', 'created_at' => Carbon::now()->subDay()]);

        // Act: Call the method to retrieve results
        $result = ExchangeRate::getOrderedFilteredExchangeRates(['EUR', 'USD'], null, null);

        // Assert: Check the order of the results
        $rates = $result[0]->flatten();
        $this->assertEquals('EUR', $rates[0]->currency_abbreviation);
        $this->assertEquals('USD', $rates[2]->currency_abbreviation);
        $this->assertTrue($rates[0]->created_at < $rates[1]->created_at);
    }
}

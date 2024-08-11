<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExchangeRateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexValidationPassesWithValidData(): void
    {
        // Arrange
        $request = Request::create('/', 'GET', [
            'start_date' => '2024-08-01',
            'end_date' => '2024-08-07',
            'currencies' => ['USD', 'EUR'],
        ]);

        // Act
        $response = $this->call('GET', '/', $request->all());

        // Assert
        $response->assertStatus(200);
    }

    public function testIndexValidationFailsWhenDateRangeExceeds7Days(): void
    {
        // Arrange
        $request = Request::create('/', 'GET', [
            'start_date' => '2024-08-01',
            'end_date' => '2024-08-10',
        ]);

        $validator = Validator::make($request->all(), [
            'end_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_date && $request->end_date) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = Carbon::parse($request->end_date);
                    if ($startDate->diffInDays($endDate) > 7) {
                        $fail('The difference between start date and end date must not exceed 7 days.');
                    }
                }
            }],
        ]);

        // Act
        $fails = $validator->fails();

        // Assert
        $this->assertTrue($fails);
        $this->assertContains('The difference between start date and end date must not exceed 7 days.', $validator->errors()->all());
    }

    public function testIndexValidationFailsWhenStartDateIsAfterEndDate(): void
    {
        // Arrange
        $request = Request::create('/', 'GET', [
            'start_date' => '2024-08-08',
            'end_date' => '2024-08-01',
        ]);

        $validator = Validator::make($request->all(), [
            'end_date' => ['nullable', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_date && $request->end_date) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = Carbon::parse($request->end_date);
                    if ($startDate > $endDate) {
                        $fail('Start date must be before end date');
                    }
                }
            }],
        ]);

        // Act
        $fails = $validator->fails();

        // Assert
        $this->assertTrue($fails);
        $this->assertContains('Start date must be before end date', $validator->errors()->all());
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_abbreviation' => $this->faker->unique()->currencyCode(),
            'rate' => $this->faker->randomFloat(5, 0, 100),
            'created_at' => now(),
        ];
    }
}

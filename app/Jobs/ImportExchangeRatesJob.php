<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use PDO;

class ImportExchangeRatesJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    private Carbon $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /* This script fetches exchange rates from the European Central Bank's API, parses the CSV data, and inserts the
         rates into a MySQL database. It uses a transaction to ensure all rates are inserted successfully. If an error
         occurs during data retrieval or database operations, it handles the error gracefully and optionally notifies the
         admin. */

        $client = new Client();

        try {
            $url = 'https://www.bank.lv/vk/ecb.csv?date=' . $this->date->format('Ymd');
            $response = $client->request('GET', $url);
            $content = $response->getBody()->getContents();
        } catch (\Exception $e) {
            if ($admin = User::find(config('app.admin_id'))) {
                // email service is needed to complete notify
                // then $admin->notify(new ...); can be used
            }
        }

        if (isset($content)) {
            $lines = array_filter(array_map('trim', explode("\n", $content)));
            $currencyRates = [];

            foreach ($lines as $line) {
                [$currency, $rate] = explode("\t", $line);
                $currencyRates[$currency] = (float) $rate;
            }

            $host = config('database.connections.mysql.host');
            $dbname = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');

            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $this->fail("Connection failed: " . $e->getMessage());
            }

            $sql = "INSERT INTO exchange_rates (currency_abbreviation, rate, created_at) VALUES (:currency_abbreviation, :rate, :created_at)";
            $stmt = $pdo->prepare($sql);
            $dateString = $this->date->toDateString();

            $pdo->beginTransaction();

            try {
                foreach ($currencyRates as $currency => $rate) {
                    $stmt->bindParam(':currency_abbreviation', $currency);
                    $stmt->bindParam(':rate', $rate);
                    $stmt->bindParam(':created_at', $dateString);

                    $stmt->execute();
                }

                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
            }
        } else {
            $this->fail('Failed to get Exchange Rates');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        /* Failed Jobs will also appear in Horizon panel */
        if ($admin = User::find(config('app.admin_id'))) {
            // email service is needed
            // then $admin->notify(new ...); can be used
        }
    }
}

<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CurrencyService
{
    /**
     * Fetch exchange rates from exchangerate-api.io API.
     *
     * @return array|null Returns array of rates or null on failure
     */
    public function fetchRatesFromAPI(): ?array {
        try {
            $apiKey = config( 'services.exchange_rate.api_key' );
            $baseCurrency = config( 'services.exchange_rate.base_currency', 'USD' );

            if ( empty( $apiKey ) ) {
                Log::warning( 'Exchange rate API key is not configured' );
                return null;
            }

            // exchangerate-api.io endpoint
            $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$baseCurrency}";

            $response = Http::timeout( 10 )->get( $url );

            if ( ! $response->successful() ) {
                Log::error( 'Failed to fetch exchange rates from API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ] );
                return null;
            }

            $data = $response->json();

            // Check if the API returned a successful result
            if ( ! isset( $data['result'] ) || $data['result'] !== 'success' ) {
                Log::error( 'Exchange rate API returned unsuccessful result', [
                    'data' => $data,
                ] );
                return null;
            }

            // Return the conversion_rates array
            return $data['conversion_rates'] ?? null;

        } catch ( Exception $e ) {
            Log::error( 'Exception while fetching exchange rates from API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ] );
            return null;
        }
    }

    /**
     * Update exchange rates in the database from API.
     *
     * @return bool Returns true on success, false on failure
     */
    public function updateRates(): bool {
        $rates = $this->fetchRatesFromAPI();

        if ( $rates === null ) {
            Log::warning( 'Could not update exchange rates: API fetch failed' );
            return false;
        }

        $updated = 0;
        $baseCurrency = config( 'services.exchange_rate.base_currency', 'USD' );

        // Get currencies we care about from the helper
        $supportedCurrencies = array_column( \App\Helpers\Currencies::getCurrencies(), 'code' );

        foreach ( $supportedCurrencies as $currencyCode ) {
            if ( ! isset( $rates[ $currencyCode ] ) ) {
                Log::warning( "Exchange rate for {$currencyCode} not found in API response" );
                continue;
            }

            $rate = $rates[ $currencyCode ];

            // Update or create the exchange rate record
            ExchangeRate::updateOrCreate(
                [ 'currency_code' => $currencyCode ],
                [
                    'rate' => $rate,
                    'base_currency' => $baseCurrency,
                    'last_updated_at' => now(),
                ]
            );

            // Clear cache for this currency
            ExchangeRate::clearCache( $currencyCode );

            $updated++;
        }

        Log::info( "Successfully updated {$updated} exchange rates from API" );

        return $updated > 0;
    }

    /**
     * Get the exchange rate for a specific currency.
     * Falls back to hardcoded rates if database is empty.
     *
     * @param string $currencyCode
     * @return float
     */
    public function getRate( string $currencyCode ): float {
        // Try to get rate from database (with cache)
        $rate = ExchangeRate::getRate( $currencyCode );

        if ( $rate !== null ) {
            return $rate;
        }

        // Fallback to hardcoded rates
        $hardcodedRates = [
            'USD' => 1.0,
            'BRL' => 5.0,
            'COP' => 4000.0,
        ];

        if ( isset( $hardcodedRates[ $currencyCode ] ) ) {
            Log::warning( "Using hardcoded fallback rate for {$currencyCode}" );
            return $hardcodedRates[ $currencyCode ];
        }

        // Ultimate fallback: return 1.0 (same as USD)
        Log::error( "No exchange rate found for {$currencyCode}, using 1.0 as fallback" );
        return 1.0;
    }

    /**
     * Convert an amount from one currency to another.
     *
     * @param float $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function convert( float $amount, string $fromCurrency, string $toCurrency ): float {
        if ( $fromCurrency === $toCurrency ) {
            return $amount;
        }

        // Convert from source currency to USD first
        $fromRate = $this->getRate( $fromCurrency );
        $amountInUSD = $amount / $fromRate;

        // Convert from USD to target currency
        $toRate = $this->getRate( $toCurrency );
        return $amountInUSD * $toRate;
    }

    /**
     * Convert an amount from USD to target currency.
     *
     * @param float $amountUSD
     * @param string $targetCurrency
     * @return float
     */
    public function convertFromUSD( float $amountUSD, string $targetCurrency ): float {
        return $this->convert( $amountUSD, 'USD', $targetCurrency );
    }

    /**
     * Get the timestamp when rates were last updated.
     *
     * @return \Carbon\Carbon|null
     */
    public function getLastUpdateTime(): ?\Carbon\Carbon {
        $latestRate = ExchangeRate::orderBy( 'last_updated_at', 'desc' )->first();
        return $latestRate ? $latestRate->last_updated_at : null;
    }

    /**
     * Check if any exchange rates are stale (older than 25 hours).
     *
     * @return bool
     */
    public function hasStaleRates(): bool {
        return ExchangeRate::staleRates()->count() > 0;
    }
}

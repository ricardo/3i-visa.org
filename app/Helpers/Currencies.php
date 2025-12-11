<?php

namespace App\Helpers;

use App\Services\CurrencyService;

class Currencies {

	/**
	 * Hardcoded exchange rates as emergency fallback
	 * (only used if database is empty)
	 *
	 * @var array
	 */
	private static $fallback_rates = [
		'USD' => 1,
		'BRL' => 5.0,    // 1 USD = 5.0 BRL
		'COP' => 4000,   // 1 USD = 4000 COP
	];

	/**
	 * Get list of supported currencies with formatting configuration
	 *
	 * @return array Array of currencies with formatting options
	 */
	public static function getCurrencies() {
		return [
			[
				'name' => 'USD $',
				'code' => 'USD',
				'symbol' => '$',
				'flag' => 'us',
				'decimal_places' => 2,
				'thousands_separator' => ',',
				'decimal_separator' => '.',
				'symbol_position' => 'before', // 'before' or 'after'
			],
			[
				'name' => 'BRL R$',
				'code' => 'BRL',
				'symbol' => 'R$',
				'flag' => 'br',
				'decimal_places' => 2,
				'thousands_separator' => '.',
				'decimal_separator' => ',',
				'symbol_position' => 'before',
			],
			[
				'name' => 'COP $',
				'code' => 'COP',
				'symbol' => '$',
				'flag' => 'co',
				'decimal_places' => 0, // No decimals for Colombian Pesos
				'thousands_separator' => '.',
				'decimal_separator' => ',',
				'symbol_position' => 'before',
			],
		];
	}

	/**
	 * Get currency symbol by currency code
	 *
	 * @param string $code Currency code (USD, BRL, COP)
	 * @return string Currency symbol
	 */
	public static function getSymbol( $code ) {
		$currencies = self::getCurrencies();
		foreach ( $currencies as $currency ) {
			if ( $currency['code'] === $code ) {
				return $currency['symbol'];
			}
		}
		return '$'; // Default to USD symbol
	}

	/**
	 * Get exchange rate for a currency (1 USD = X target currency).
	 * Uses database rates from CurrencyService, with fallback to hardcoded rates.
	 *
	 * @param string $target_currency Target currency code (USD, BRL, COP)
	 * @return float Exchange rate
	 */
	public static function getExchangeRate( $target_currency ) {
		if ( $target_currency === 'USD' ) {
			return 1.0;
		}

		// Try to get from database
		$rate = \App\Models\ExchangeRate::getRate( $target_currency );

		// Fallback to hardcoded rates
		if ( $rate === null ) {
			$rate = self::$fallback_rates[ $target_currency ] ?? 1.0;
		}

		return $rate;
	}

	/**
	 * Convert amount from USD to target currency.
	 * Uses database rates from CurrencyService, with fallback to hardcoded rates.
	 *
	 * @param float $amount_usd Amount in USD
	 * @param string $target_currency Target currency code (USD, BRL, COP)
	 * @return float Converted amount
	 */
	public static function convertFromUSD( $amount_usd, $target_currency ) {
		// Use CurrencyService for conversion (which handles caching and fallbacks)
		$currencyService = app( CurrencyService::class );
		return $currencyService->convertFromUSD( $amount_usd, $target_currency );
	}

	/**
	 * Get the timestamp when exchange rates were last updated.
	 *
	 * @return \Carbon\Carbon|null
	 */
	public static function getLastUpdateTime(): ?\Carbon\Carbon {
		$currencyService = app( CurrencyService::class );
		return $currencyService->getLastUpdateTime();
	}

	/**
	 * Get currency configuration by currency code.
	 *
	 * @param string $code Currency code (USD, BRL, COP)
	 * @return array|null Currency configuration or null if not found
	 */
	public static function getCurrencyConfig( string $code ): ?array {
		$currencies = self::getCurrencies();
		foreach ( $currencies as $currency ) {
			if ( $currency['code'] === $code ) {
				return $currency;
			}
		}
		return null;
	}

	/**
	 * Format a currency amount according to currency-specific rules.
	 *
	 * @param float $amount The amount to format
	 * @param string $currency_code Currency code (USD, BRL, COP)
	 * @param bool $include_symbol Whether to include the currency symbol
	 * @return string Formatted currency amount
	 */
	public static function format( float $amount, string $currency_code, bool $include_symbol = true ): string {
		$config = self::getCurrencyConfig( $currency_code );

		if ( ! $config ) {
			// Fallback formatting if currency not found
			return $include_symbol ? '$' . number_format( $amount, 2, '.', ',' ) : number_format( $amount, 2, '.', ',' );
		}

		// Format the number according to currency config
		$formatted_number = number_format(
			$amount,
			$config['decimal_places'],
			$config['decimal_separator'],
			$config['thousands_separator']
		);

		// Add symbol if requested
		if ( $include_symbol ) {
			if ( $config['symbol_position'] === 'after' ) {
				return $formatted_number . ' ' . $config['symbol'];
			} else {
				return $config['symbol'] . $formatted_number;
			}
		}

		return $formatted_number;
	}

	/**
	 * Convert amount from USD to target currency and format it.
	 * Convenience method that combines conversion and formatting.
	 *
	 * @param float $amount_usd Amount in USD
	 * @param string $target_currency Target currency code (USD, BRL, COP)
	 * @param bool $include_symbol Whether to include the currency symbol
	 * @return string Formatted currency amount
	 */
	public static function convertFromUSDFormatted( float $amount_usd, string $target_currency, bool $include_symbol = true ): string {
		$converted = self::convertFromUSD( $amount_usd, $target_currency );
		return self::format( $converted, $target_currency, $include_symbol );
	}

}

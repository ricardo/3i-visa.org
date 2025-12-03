<?php

namespace App\Helpers;

class Currencies {

	/**
	 * Exchange rates relative to USD (1 USD = X currency)
	 *
	 * @var array
	 */
	private static $exchange_rates = [
		'USD' => 1,
		'BRL' => 5.0,    // 1 USD = 5.0 BRL
		'COP' => 4000,   // 1 USD = 4000 COP
	];

	/**
	 * Get list of supported currencies
	 *
	 * @return array Array of currencies with 'name', 'code', and 'symbol' keys
	 */
	public static function getCurrencies() {
		return [
			[ 'name' => 'USD $', 'code' => 'USD', 'symbol' => '$', 'flag' => 'us' ],
			[ 'name' => 'BRL R$', 'code' => 'BRL', 'symbol' => 'R$', 'flag' => 'br' ],
			[ 'name' => 'COP $', 'code' => 'COP', 'symbol' => 'COL$', 'flag' => 'co' ],
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
	 * Convert amount from USD to target currency
	 *
	 * @param float $amount_usd Amount in USD
	 * @param string $target_currency Target currency code (USD, BRL, COP)
	 * @return float Converted amount
	 */
	public static function convertFromUSD( $amount_usd, $target_currency ) {
		if ( ! isset( self::$exchange_rates[ $target_currency ] ) ) {
			return $amount_usd; // Return original if currency not found
		}

		$rate = self::$exchange_rates[ $target_currency ];
		return $amount_usd * $rate;
	}

}

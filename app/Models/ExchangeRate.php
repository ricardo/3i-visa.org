<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExchangeRate extends Model {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'currency_code',
		'rate',
		'base_currency',
		'last_updated_at',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = [
		'rate' => 'decimal:8',
		'last_updated_at' => 'datetime',
	];

	/**
	 * Get the exchange rate for a specific currency code.
	 * Results are cached for 30 minutes for performance.
	 *
	 * @param string $currencyCode
	 * @return float|null
	 */
	public static function getRate( string $currencyCode ): ?float {
		$cacheKey = "exchange_rate_{$currencyCode}";

		return Cache::remember( $cacheKey, now()->addMinutes( 30 ), function () use ( $currencyCode ) {
			$exchangeRate = self::where( 'currency_code', $currencyCode )->first();
			return $exchangeRate ? (float) $exchangeRate->rate : null;
		} );
	}

	/**
	 * Check if the exchange rate is stale (older than 25 hours).
	 * This helps identify when rates need updating.
	 *
	 * @return bool
	 */
	public function isStale(): bool {
		if ( ! $this->last_updated_at ) {
			return true;
		}

		return $this->last_updated_at->lt( now()->subHours( 25 ) );
	}

	/**
	 * Get all rates that are stale (older than 25 hours).
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function staleRates() {
		return self::where( 'last_updated_at', '<', now()->subHours( 25 ) )
			->orWhereNull( 'last_updated_at' )
			->get();
	}

	/**
	 * Clear the cache for a specific currency code.
	 *
	 * @param string $currencyCode
	 * @return void
	 */
	public static function clearCache( string $currencyCode ): void {
		Cache::forget( "exchange_rate_{$currencyCode}" );
	}

	/**
	 * Clear the cache for all currency codes.
	 *
	 * @return void
	 */
	public static function clearAllCache(): void {
		$currencies = self::pluck( 'currency_code' );
		foreach ( $currencies as $currencyCode ) {
			self::clearCache( $currencyCode );
		}
	}
}

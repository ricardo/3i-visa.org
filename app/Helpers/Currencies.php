<?php

namespace App\Helpers;

class Currencies {

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

}

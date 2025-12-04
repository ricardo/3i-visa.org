<?php

namespace App\Helpers;

class Countries {

	/**
	 * Get priority country codes (main visa destinations)
	 *
	 * @return array Array of priority country codes
	 */
	public static function getPriorityCountries() {
		return [ 'br', 'us', 'ca', 'co', 'au', 'fr', 'de', 'gb' ];
	}

	/**
	 * Get all country codes
	 *
	 * @return array Array of all ISO country codes
	 */
	private static function getAllCountryCodes() {
		return [
			'af', 'al', 'dz', 'ad', 'ao', 'ag', 'ar', 'am', 'au', 'at',
			'az', 'bs', 'bh', 'bd', 'bb', 'by', 'be', 'bz', 'bj', 'bt',
			'bo', 'ba', 'bw', 'br', 'bn', 'bg', 'bf', 'bi', 'kh', 'cm',
			'ca', 'cv', 'cf', 'td', 'cl', 'cn', 'co', 'km', 'cg', 'cr',
			'hr', 'cu', 'cy', 'cz', 'dk', 'dj', 'dm', 'do', 'tl', 'ec',
			'eg', 'sv', 'gq', 'er', 'ee', 'et', 'fj', 'fi', 'fr', 'ga',
			'gm', 'ge', 'de', 'gh', 'gr', 'gd', 'gt', 'gn', 'gw', 'gy',
			'ht', 'hn', 'hu', 'is', 'in', 'id', 'ir', 'iq', 'ie', 'il',
			'it', 'ci', 'jm', 'jp', 'jo', 'kz', 'ke', 'ki', 'kw', 'kg',
			'la', 'lv', 'lb', 'ls', 'lr', 'ly', 'li', 'lt', 'lu', 'mk',
			'mg', 'mw', 'my', 'mv', 'ml', 'mt', 'mh', 'mr', 'mu', 'mx',
			'fm', 'md', 'mc', 'mn', 'me', 'ma', 'mz', 'mm', 'na', 'nr',
			'np', 'nl', 'nz', 'ni', 'ne', 'ng', 'kp', 'no', 'om', 'pk',
			'pw', 'ps', 'pa', 'pg', 'py', 'pe', 'ph', 'pl', 'pt', 'qa',
			'ro', 'ru', 'rw', 'kn', 'lc', 'vc', 'ws', 'sm', 'st', 'sa',
			'sn', 'rs', 'sc', 'sl', 'sg', 'sk', 'si', 'sb', 'so', 'za',
			'kr', 'ss', 'es', 'lk', 'sd', 'sr', 'sz', 'se', 'ch', 'sy',
			'tw', 'tj', 'tz', 'th', 'tg', 'to', 'tt', 'tn', 'tr', 'tm',
			'tv', 'ug', 'ua', 'ae', 'gb', 'us', 'uy', 'uz', 'vu', 'va',
			've', 'vn', 'ye', 'zm', 'zw',
		];
	}

	/**
	 * Get list of all countries with ISO codes, translated and sorted
	 *
	 * Priority countries (visa destinations) appear first, followed by
	 * all other countries sorted alphabetically by translated name
	 *
	 * @return array Array of countries with 'name' and 'code' keys
	 */
	public static function getCountries() {
		$country_codes = self::getAllCountryCodes();
		$priority_codes = self::getPriorityCountries();
		$translations = trans('countries');

		// Build array with country code => translated name
		$countries = [];
		foreach ( $country_codes as $code ) {
			if ( isset( $translations[ $code ] ) ) {
				$countries[] = [
					'name' => $translations[ $code ],
					'code' => $code,
					'is_priority' => in_array( $code, $priority_codes ),
				];
			}
		}

		// Sort: priority countries first, then alphabetically by name
		usort( $countries, function ( $a, $b ) {
			// Priority countries come first
			if ( $a['is_priority'] && ! $b['is_priority'] ) {
				return -1;
			}
			if ( ! $a['is_priority'] && $b['is_priority'] ) {
				return 1;
			}

			// Within same priority level, sort alphabetically
			return strcasecmp( $a['name'], $b['name'] );
		} );

		// Remove the is_priority flag before returning
		$countries = array_map( function ( $country ) {
			return [
				'name' => $country['name'],
				'code' => $country['code'],
			];
		}, $countries );

		return $countries;
	}

	/**
	 * Get list of main visa destination countries
	 *
	 * @return array Array of visa destination countries with 'name' and 'code' keys
	 */
	public static function getVisaCountries() {
		$priority_codes = self::getPriorityCountries();
		$translations = trans('countries');
		$countries = [];

		foreach ( $priority_codes as $code ) {
			if ( isset( $translations[ $code ] ) ) {
				$countries[] = [
					'name' => $translations[ $code ],
					'code' => $code,
				];
			}
		}

		// Sort alphabetically by translated name
		usort( $countries, function ( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		} );

		return $countries;
	}

	/**
	 * Get translated name for a specific country code
	 *
	 * @param string $code ISO country code
	 * @return string Translated country name or empty string if not found
	 */
	public static function getCountryName( $code ) {
		$translations = trans('countries');
		return $translations[ $code ] ?? '';
	}

	/**
	 * Get country code to slug mapping for visa destinations
	 *
	 * @return array Associative array of country code => slug
	 */
	public static function getCountrySlugs() {
		return [
			'us' => 'united-states',
			'gb' => 'united-kingdom',
			'au' => 'australia',
			'ca' => 'canada',
			'fr' => 'france',
			'de' => 'germany',
			'br' => 'brazil',
			'co' => 'colombia',
		];
	}

}

<?php

if ( ! function_exists( 'is_crawler_bot' ) ) {
	function is_crawler_bot( $request ) {
		$user_agent = strtolower( $request->userAgent() ?? '' );

		$bot_patterns = [
			'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
			'yandexbot', 'facebookexternalhit', 'twitterbot', 'rogerbot',
			'linkedinbot', 'embedly', 'quora link preview', 'showyoubot',
			'outbrain', 'pinterest/0.', 'developers.google.com/+/web/snippet',
			'slackbot', 'vkshare', 'w3c_validator', 'redditbot', 'applebot',
			'whatsapp', 'flipboard', 'tumblr', 'bitlybot', 'skypeuripreview',
			'nuzzel', 'discordbot', 'google page speed', 'qwantify'
		];

		foreach ( $bot_patterns as $pattern ) {
			if ( strpos( $user_agent, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'get_current_path_without_locale' ) ) {
	function get_current_path_without_locale( $url = null ) {
		if ( $url === null ) {
			$url = request()->fullUrl();
		}

		$parsed_url = parse_url( $url );
		$path = $parsed_url['path'] ?? '/';

		// Remove current locale prefix if it exists.
		$current_locale = request()->segment( 1 );
		$supported_locales = array_keys( config( 'app.supported_locales' ) );

		if ( in_array( $current_locale, $supported_locales ) && $current_locale !== 'en' ) {
			$path = preg_replace( '#^/' . preg_quote( $current_locale, '#' ) . '(?=/|$)#', '', $path );
		}

		return $path ?: '/';
	}
}

if ( ! function_exists( 'get_localized_url' ) ) {
	function get_localized_url( $locale, $path ) {
		// Ensure path starts with /.
		if ( ! str_starts_with( $path, '/' ) ) {
			$path = '/' . $path;
		}

		// Build URL based on locale.
		if ( $locale === 'en' ) {
			$url = $path;
		} else {
			$url = '/' . $locale . $path;
		}

		// Add query parameters.
		$query_params = request()->query();
		if ( ! empty( $query_params ) ) {
			$url .= '?' . http_build_query( $query_params );
		}

		return $url;
	}
}

if ( ! function_exists( 'generate_hreflang_tags' ) ) {
	function generate_hreflang_tags( $url = null ) {
		$supported_locales = config( 'app.supported_locales' );
		$current_path = get_current_path_without_locale( $url );

		$hreflang_tags = '';

		foreach ( $supported_locales as $locale => $language_name ) {
			$localized_url = get_localized_url( $locale, $current_path );
			$full_url = url( $localized_url );

			$hreflang_tags .= '<link rel="alternate" hreflang="' . $locale . '" href="' . $full_url . '">' . "\n";
		}

		// Add x-default for English.
		$default_url = get_localized_url( 'en', $current_path );
		$hreflang_tags .= '<link rel="alternate" hreflang="x-default" href="' . url( $default_url ) . '">' . "\n";

		return $hreflang_tags;
	}
}
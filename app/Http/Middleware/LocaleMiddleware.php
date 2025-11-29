<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;


class LocaleMiddleware {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle( Request $request, Closure $next ): Response {

		// Temporary enforcement: force everything to /pt
		$path = trim( $request->getPathInfo(), '/' );

		// If path starts with webhook, skip
		if ( str_starts_with( $path, 'webhook' ) ) {
			return $next( $request );
		}

		$segment            = $request->segment( 1 );
		$supported_locales  = array_keys( config( 'app.supported_locales' ) );
		$is_bot             = is_crawler_bot( $request );
		$minutes            = 60 * 24 * 180; // 180 days.

		// One-time enforcement right after login.
		if ( $forced = $request->session()->pull( 'force_account_locale' ) ) {
			$src = $request->session()->pull( 'force_account_locale_src' ) ?: 'account';

			app()->setLocale( $forced );

			if ( ! $is_bot ) {
				Cookie::queue( 'preferred_locale', $forced, $minutes );
				Cookie::queue( 'preferred_locale_src', $src, $minutes );
				Cookie::queue( 'preferred_locale_ts', (string) now()->timestamp, $minutes );
			}

			// Normalize URL to forced locale ("/" for en, "/{locale}" otherwise).
			$path_no_locale = get_current_path_without_locale( $request->getPathInfo() );
			$target_path    = $forced === 'en' ? $path_no_locale : '/' . $forced . $path_no_locale;

			$current_path   = rtrim( $request->getPathInfo(), '/' );
			$target_path    = rtrim( $target_path, '/' );

			if ( $current_path !== $target_path ) {
				$suffix = $request->getQueryString();
				return redirect()->to( $target_path . ( $suffix ? '?' . $suffix : '' ) );
			}

			return $next( $request );
		}

		$cookie_locale = $request->cookie( 'preferred_locale' );
		$cookie_src    = $request->cookie( 'preferred_locale_src' ); // 'manual' | 'account' | 'url' | null

		// If first segment is a supported locale, use it for this request.
		if ( $segment && in_array( $segment, $supported_locales ) ) {
			app()->setLocale( $segment );

			// Only persist URL-based locale when there's no stronger preference stored.
			if ( ! $is_bot && ! in_array( $cookie_src, [ 'manual', 'account' ] ) ) {
				Cookie::queue( 'preferred_locale', $segment, $minutes );
				Cookie::queue( 'preferred_locale_src', 'url', $minutes );
				Cookie::queue( 'preferred_locale_ts', (string) now()->timestamp, $minutes );
			}

			return $next( $request );
		}

		// No locale in URL: honor stored preference.
		if ( ! $is_bot ) {
			$stored_locale = $cookie_locale;

			// If user has a stored preference and it's not 'en', redirect to prefixed path.
			// (Manual 'en' => stays at '/')
			if ( $stored_locale && in_array( $stored_locale, $supported_locales ) && $stored_locale !== 'en' ) {
				$redirect_url = '/' . $stored_locale . $request->getRequestUri();
				return redirect( $redirect_url );
			}
		}

		// Default locale.
		app()->setLocale( 'en' );

		return $next( $request );
	}
}

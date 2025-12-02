<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller {

	public function getHomePage() {
		return view( 'pages.home' );
	}

	public function postSwitchLanguage( Request $request ) {
		$new_locale = $request->input( 'locale' );
		$supported_locales = array_keys( config( 'app.supported_locales' ) );

		// Validate new locale.
		if ( ! in_array( $new_locale, $supported_locales ) ) {
			return redirect()->back();
		}

		// Update cookie if not a bot.
		if ( ! is_crawler_bot( $request ) ) {
			$minutes = 60 * 24 * 180; // 180 days.

			Cookie::queue( 'preferred_locale', $new_locale, $minutes );
			Cookie::queue( 'preferred_locale_src', 'manual', $minutes );
			// Optional: keep a timestamp, useful if you later want to compare recency.
			Cookie::queue( 'preferred_locale_ts', (string) now()->timestamp, $minutes );
		}

		// Get the current path without locale.
		$current_path = get_current_path_without_locale( route( $request->input( 'current_route' ) ) );
		$new_url      = get_localized_url( $new_locale, $current_path );

		// Add locale to user's profile if logged in.
		$user = auth()->check() ? auth()->user() : null;
		if ( $user && $user->locale !== $new_locale ) {
			$user->update( [ 'locale' => $new_locale ] );
		}

		return redirect( $new_url );
	}

	public function postSavePreferences( Request $request ) {
		$new_locale = $request->input( 'locale' );
		$new_currency = $request->input( 'currency' );
		$supported_locales = array_keys( config( 'app.supported_locales' ) );
		$supported_currencies = [ 'USD', 'BRL', 'COP' ]; // Match Currencies helper.

		// Validate new locale.
		if ( ! in_array( $new_locale, $supported_locales ) ) {
			return redirect()->back();
		}

		// Validate new currency.
		if ( ! in_array( $new_currency, $supported_currencies ) ) {
			return redirect()->back();
		}

		// Update cookies if not a bot.
		if ( ! is_crawler_bot( $request ) ) {
			$minutes = 60 * 24 * 180; // 180 days.

			// Save locale cookies.
			Cookie::queue( 'preferred_locale', $new_locale, $minutes );
			Cookie::queue( 'preferred_locale_src', 'manual', $minutes );
			Cookie::queue( 'preferred_locale_ts', (string) now()->timestamp, $minutes );

			// Save currency cookie.
			Cookie::queue( 'preferred_currency', $new_currency, $minutes );
			Cookie::queue( 'preferred_currency_src', 'manual', $minutes );
			Cookie::queue( 'preferred_currency_ts', (string) now()->timestamp, $minutes );
		}

		// Get the current path without locale.
		$current_path = get_current_path_without_locale( route( $request->input( 'current_route' ) ) );
		$new_url      = get_localized_url( $new_locale, $current_path );

		// Add locale to user's profile if logged in.
		$user = auth()->check() ? auth()->user() : null;
		if ( $user && $user->locale !== $new_locale ) {
			$user->update( [ 'locale' => $new_locale ] );
		}

		return redirect( $new_url );
	}

	public function postVisaCheck( Request $request ) {
		$passport    = $request->input( 'passport' );
		$destination = $request->input( 'destination' );

		// Validate inputs.
		$valid_countries = [ 'au', 'br', 'ca', 'co', 'fr', 'de', 'gb', 'us' ];

		if ( ! in_array( $passport, $valid_countries ) || ! in_array( $destination, $valid_countries ) ) {
			return redirect()->back()->withErrors( [
				'visa_check' => __( 'Please select valid countries for both passport and destination.' )
			] );
		}

		// Prevent same country selection.
		if ( $passport === $destination ) {
			return redirect()->back()->withErrors( [
				'visa_check' => __( 'Your passport country and destination cannot be the same.' )
			] );
		}

		// TODO: Implement visa requirements lookup and redirect to results page.
		// For now, redirect back with success message.
		return redirect()->back()->with( 'success', __( 'Visa check successful! Passport: :passport, Destination: :destination', [
			'passport' => strtoupper( $passport ),
			'destination' => strtoupper( $destination ),
		] ) );
	}
}

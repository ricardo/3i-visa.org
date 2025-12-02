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
				'nationality' => __( 'Please select a valid country.' )
			] );
		}

		// Prevent same country selection.
		if ( $passport === $destination ) {
			return redirect()->back()->withErrors( [
				'destination' => __( 'Your passport country and destination cannot be the same.' )
			] );
		}

		// Country code to slug mapping.
		$country_slugs = [
			'au' => 'australia',
			'br' => 'brazil',
			'ca' => 'canada',
			'co' => 'colombia',
			'fr' => 'france',
			'de' => 'germany',
			'gb' => 'united-kingdom',
			'us' => 'united-states',
		];

		// Store nationality (passport) in session.
		$request->session()->put( 'visa_application', [
			'nationality' => $passport,
			'destination' => $destination,
		] );

		// Get destination slug.
		$destination_slug = $country_slugs[ $destination ] ?? $destination;

		// Redirect to apply page.
		return redirect()->route( 'apply', [ 'country' => $destination_slug ] );
	}

	public function getApplyPage( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = [
			'australia' => 'au',
			'brazil' => 'br',
			'canada' => 'ca',
			'colombia' => 'co',
			'france' => 'fr',
			'germany' => 'de',
			'united-kingdom' => 'gb',
			'united-states' => 'us',
		];

		// Country code to name mapping.
		$country_names = [
			'au' => 'Australia',
			'br' => 'Brazil',
			'ca' => 'Canada',
			'co' => 'Colombia',
			'fr' => 'France',
			'de' => 'Germany',
			'gb' => 'United Kingdom',
			'us' => 'United States',
		];

		// Get country code from slug.
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Get visa application data from session.
		$visa_data = $request->session()->get( 'visa_application', [] );
		$nationality = $visa_data['nationality'] ?? null;

		// If no nationality in session, redirect to home.
		if ( ! $nationality ) {
			return redirect()->route( 'home' );
		}

		// Get country list for dropdown.
		$visa_countries = [
			[ 'name' => 'Australia', 'code' => 'au' ],
			[ 'name' => 'Brazil', 'code' => 'br' ],
			[ 'name' => 'Canada', 'code' => 'ca' ],
			[ 'name' => 'Colombia', 'code' => 'co' ],
			[ 'name' => 'France', 'code' => 'fr' ],
			[ 'name' => 'Germany', 'code' => 'de' ],
			[ 'name' => 'United Kingdom', 'code' => 'gb' ],
			[ 'name' => 'United States', 'code' => 'us' ],
		];

		// Find pre-selected nationality.
		$selected_nationality = null;
		foreach ( $visa_countries as $c ) {
			if ( $c['code'] === $nationality ) {
				$selected_nationality = $c;
				break;
			}
		}

		return view( 'pages.apply', [
			'country_name' => $country_names[ $country_code ],
			'country_code' => $country_code,
			'country_slug' => $country,
			'visa_countries' => $visa_countries,
			'selected_nationality' => $selected_nationality,
		] );
	}

	public function postApplyPage( Request $request, $country ) {
		// Validate inputs.
		$request->validate( [
			'nationality' => 'required|string|in:au,br,ca,co,fr,de,gb,us',
			'applicants' => 'required|integer|min:1|max:10',
		] );

		$nationality = $request->input( 'nationality' );
		$applicants = $request->input( 'applicants' );

		// Store in session.
		$request->session()->put( 'visa_application.applicants', $applicants );
		$request->session()->put( 'visa_application.nationality', $nationality );

		// TODO: Redirect to next step (application form).
		// For now, redirect back with success message.
		return redirect()->back()->with( 'success', __( 'Application started! Applicants: :count', [
			'count' => $applicants,
		] ) );
	}
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Countries;
use App\Helpers\Currencies;
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
		$route_name = $request->input( 'current_route' );
		$route_params = json_decode( $request->input( 'route_params', '{}' ), true ) ?? [];
		$current_path = get_current_path_without_locale( route( $route_name, $route_params ) );
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
		$route_name = $request->input( 'current_route' );
		$route_params = json_decode( $request->input( 'route_params', '{}' ), true ) ?? [];
		$current_path = get_current_path_without_locale( route( $route_name, $route_params ) );
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
		$valid_countries = Countries::getPriorityCountries();

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

		// Store nationality (passport) in session.
		// Use dot notation to preserve existing data (applicants, travelers, etc.)
		$request->session()->put( 'visa_application.nationality', $passport );
		$request->session()->put( 'visa_application.destination', $destination );

		// Get destination slug.
		$country_slugs = Countries::getCountrySlugs();
		$destination_slug = $country_slugs[ $destination ] ?? $destination;

		// Redirect to apply page.
		return redirect()->route( 'apply', [ 'country' => $destination_slug ] );
	}

	public function getApplyPage( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );

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

		// Get country list for dropdown (translated).
		$visa_countries = Countries::getVisaCountries();

		// Find pre-selected nationality.
		$selected_nationality = null;
		foreach ( $visa_countries as $c ) {
			if ( $c['code'] === $nationality ) {
				$selected_nationality = $c;
				break;
			}
		}

		return view( 'pages.apply', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'visa_countries' => $visa_countries,
			'selected_nationality' => $selected_nationality,
		] );
	}

	public function postApplyPage( Request $request, $country ) {
		// Validate inputs.
		$valid_country_codes = implode( ',', Countries::getPriorityCountries() );
		$request->validate( [
			'nationality' => "required|string|in:{$valid_country_codes}",
			'applicants' => 'required|integer|min:1|max:10',
		] );

		$nationality = $request->input( 'nationality' );
		$applicants = $request->input( 'applicants' );

		// Store in session.
		$request->session()->put( 'visa_application.applicants', $applicants );
		$request->session()->put( 'visa_application.nationality', $nationality );

		// Redirect to application details page.
		return redirect()->route( 'application.details', [ 'country' => $country ] );
	}

	public function getApplicationDetails( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );

		// Get country code from slug.
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Get visa application data from session.
		$visa_data = $request->session()->get( 'visa_application', [] );
		$existing_travelers = $visa_data['travelers'] ?? [];

		// Use the explicitly set applicants count if available,
		// otherwise fall back to counting existing travelers
		$applicants_count = $visa_data['applicants']
			?? ( ! empty( $existing_travelers ) ? count( $existing_travelers ) : null );

		// If no applicants count in session, redirect to apply page.
		if ( ! $applicants_count ) {
			return redirect()->route( 'apply', [ 'country' => $country ] );
		}

		// Store country name in session for use in form.
		$country_name = Countries::getCountryName( $country_code );
		$request->session()->put( 'visa_application.destination_name', $country_name );

		// Pricing and currency conversion.
		$base_price_usd = 49; // Base price per traveler in USD.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$total_price = $price_per_traveler * $applicants_count;
		$currency_symbol = Currencies::getSymbol( $user_currency );

		return view( 'pages.application-details', [
			'country_name' => $country_name,
			'country_code' => $country_code,
			'country_slug' => $country,
			'applicants_count' => $applicants_count,
			'existing_travelers' => $existing_travelers,
			'price_per_traveler' => $price_per_traveler,
			'total_price' => $total_price,
			'currency_symbol' => $currency_symbol,
		] );
	}

	public function postApplicationDetails( Request $request, $country ) {
		// Get actual submitted travelers.
		$submitted_travelers = $request->input( 'travelers', [] );

		// Redirect back if no travelers submitted.
		if ( empty( $submitted_travelers ) ) {
			return redirect()->back()->withErrors( [
				'travelers' => __( 'Please provide at least one traveler.' )
			] );
		}

		// Build validation rules dynamically based on actual submitted traveler indices.
		$rules = [];
		$attributes = [];
		$traveler_indices = array_keys( $submitted_travelers );
		$is_first_traveler = true;

		foreach ( $traveler_indices as $index ) {
			$rules["travelers.{$index}.first_name"] = 'required|string|max:255';
			$rules["travelers.{$index}.last_name"] = 'required|string|max:255';
			$rules["travelers.{$index}.date_of_birth_month"] = 'required|integer|min:1|max:12';
			$rules["travelers.{$index}.date_of_birth_day"] = 'required|integer|min:1|max:31';
			$rules["travelers.{$index}.date_of_birth_year"] = 'required|integer|min:' . ( date( 'Y' ) - 125 ) . '|max:' . date( 'Y' );

			// Custom attribute names for cleaner error messages.
			$attributes["travelers.{$index}.first_name"] = 'first name';
			$attributes["travelers.{$index}.last_name"] = 'last name';
			$attributes["travelers.{$index}.date_of_birth_month"] = 'birth month';
			$attributes["travelers.{$index}.date_of_birth_day"] = 'birth day';
			$attributes["travelers.{$index}.date_of_birth_year"] = 'birth year';

			// Email required for first traveler only.
			if ( $is_first_traveler ) {
				$rules["travelers.{$index}.email"] = 'required|email|max:255';
				$attributes["travelers.{$index}.email"] = 'email';
				$is_first_traveler = false;
			}
		}

		// Validate inputs.
		$request->validate( $rules, [], $attributes );

		// Store traveler data in session.
		$request->session()->put( 'visa_application.travelers', $request->input( 'travelers' ) );

		// Update applicants count to match actual number of travelers
		$request->session()->put( 'visa_application.applicants', count( $request->input( 'travelers' ) ) );

		// Handle Ajax requests.
		if ( $request->ajax() || $request->wantsJson() ) {
			return response()->json( [
				'success' => true,
				'message' => __( 'Traveler information saved!' ),
				'redirect' => route( 'passport.details', [ 'country' => $country ] )
			] );
		}

		// Redirect to passport details page (next step).
		return redirect()->route( 'passport.details', [ 'country' => $country ] );
	}

	public function getPassportDetails( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );

		// Get country code from slug.
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Get visa application data from session.
		$visa_data = $request->session()->get( 'visa_application', [] );
		$travelers = $visa_data['travelers'] ?? null;

		// If no travelers in session, redirect to application details page.
		if ( ! $travelers ) {
			return redirect()->route( 'application.details', [ 'country' => $country ] );
		}

		$applicants_count = count( $travelers );
		$nationality = $visa_data['nationality'] ?? null;

		// Pricing and currency conversion.
		$base_price_usd = 49; // Base price per traveler in USD.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$currency_symbol = Currencies::getSymbol( $user_currency );

		return view( 'pages.passport-details', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'travelers' => $travelers,
			'applicants_count' => $applicants_count,
			'nationality' => $nationality,
			'price_per_traveler' => $price_per_traveler,
			'currency_symbol' => $currency_symbol,
		] );
	}

	public function postPassportDetails( Request $request, $country ) {
		// Get actual submitted travelers.
		$submitted_travelers = $request->input( 'travelers', [] );

		// Redirect back if no travelers submitted.
		if ( empty( $submitted_travelers ) ) {
			return redirect()->back()->withErrors( [
				'travelers' => __( 'Please provide traveler passport information.' )
			] );
		}

		// Build validation rules dynamically based on actual submitted traveler indices.
		$rules = [];
		$attributes = [];
		$traveler_indices = array_keys( $submitted_travelers );

		foreach ( $traveler_indices as $index ) {
			// Nationality is always required.
			$rules["travelers.{$index}.nationality"] = 'required|string|max:2';
			$attributes["travelers.{$index}.nationality"] = 'nationality';

			// Get the add_passport_later value for this traveler.
			$add_later = $request->input( "travelers.{$index}.add_passport_later" );

			// If NOT adding passport later, passport fields are required.
			if ( ! $add_later ) {
				$rules["travelers.{$index}.passport_number"] = 'required|string|max:255';
				$rules["travelers.{$index}.passport_expiration_month"] = 'required|integer|min:1|max:12';
				$rules["travelers.{$index}.passport_expiration_day"] = 'required|integer|min:1|max:31';
				$rules["travelers.{$index}.passport_expiration_year"] = 'required|integer|min:2025|max:' . now()->addYears( 20 )->format( 'Y' );

				$attributes["travelers.{$index}.passport_number"] = 'passport number';
				$attributes["travelers.{$index}.passport_expiration_month"] = 'passport expiration month';
				$attributes["travelers.{$index}.passport_expiration_day"] = 'passport expiration day';
				$attributes["travelers.{$index}.passport_expiration_year"] = 'passport expiration year';
			}
		}

		// Validate inputs.
		$request->validate( $rules, [], $attributes );

		// Get existing visa application data from session.
		$visa_data = $request->session()->get( 'visa_application', [] );

		// Merge passport data with existing traveler data.
		if ( isset( $visa_data['travelers'] ) ) {
			foreach ( $submitted_travelers as $index => $passport_data ) {
				if ( isset( $visa_data['travelers'][ $index ] ) ) {
					$visa_data['travelers'][ $index ] = array_merge(
						$visa_data['travelers'][ $index ],
						$passport_data
					);
				}
			}
		}

		// Save updated data back to session.
		$request->session()->put( 'visa_application', $visa_data );

		// Handle Ajax requests.
		if ( $request->ajax() || $request->wantsJson() ) {
			return response()->json( [
				'success' => true,
				'message' => __( 'Passport information saved!' ),
				'redirect' => route( 'processing.time', [ 'country' => $country ] )
			] );
		}

		// Redirect to processing time page.
		return redirect()->route( 'processing.time', [ 'country' => $country ] );
	}

	public function getProcessingTime( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );

		// Get country code from slug.
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Get visa application data from session.
		$visa_data = $request->session()->get( 'visa_application', [] );
		$travelers = $visa_data['travelers'] ?? null;

		// If no travelers in session, redirect to passport details page.
		if ( ! $travelers ) {
			return redirect()->route( 'passport.details', [ 'country' => $country ] );
		}

		$applicants_count = count( $travelers );

		// Get pricing configuration.
		$pricing_config = config( 'pricing.colombia' );
		$base_price_usd = $pricing_config['base_form_price_usd'];
		$processing_options = $pricing_config['processing_options'];

		// Get selected processing option from session (default to standard).
		$selected_processing = $visa_data['processing_option'] ?? 'standard';

		// Currency conversion.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$currency_symbol = Currencies::getSymbol( $user_currency );

		// Convert processing fees to user currency.
		foreach ( $processing_options as $key => $option ) {
			$processing_options[ $key ]['price_converted'] = Currencies::convertFromUSD(
				$option['price_usd'],
				$user_currency
			);
		}

		return view( 'pages.processing-time', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'applicants_count' => $applicants_count,
			'price_per_traveler' => $price_per_traveler,
			'currency_symbol' => $currency_symbol,
			'processing_options' => $processing_options,
			'selected_processing' => $selected_processing,
		] );
	}

	public function updateProcessingTime( Request $request, $country ) {
		// Validate processing option.
		$request->validate( [
			'processing_option' => 'required|string|in:standard,rush',
		] );

		$processing_option = $request->input( 'processing_option' );

		// Update session.
		$request->session()->put( 'visa_application.processing_option', $processing_option );

		// Get pricing configuration.
		$pricing_config = config( 'pricing.colombia' );
		$selected_option = $pricing_config['processing_options'][ $processing_option ];

		// Get user currency and convert prices.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$processing_fee = Currencies::convertFromUSD( $selected_option['price_usd'], $user_currency );

		// Return success with updated pricing.
		return response()->json( [
			'success' => true,
			'processing_fee' => $processing_fee,
		] );
	}

	public function postProcessingTime( Request $request, $country ) {
		// Validate processing option.
		$request->validate( [
			'processing_option' => 'required|string|in:standard,rush',
		] );

		$processing_option = $request->input( 'processing_option' );

		// Update session.
		$request->session()->put( 'visa_application.processing_option', $processing_option );

		// Handle Ajax requests.
		if ( $request->ajax() || $request->wantsJson() ) {
			return response()->json( [
				'success' => true,
				'message' => __( 'Processing time saved!' ),
				'redirect' => route( 'processing.time', [ 'country' => $country ] ) // TODO: Update to next step route
			] );
		}

		// TODO: Redirect to next step (payment or confirmation).
		// For now, redirect back with success message.
		return redirect()->back()->with( 'success', __( 'Processing time saved!' ) );
	}
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Countries;
use App\Helpers\Currencies;
use App\Services\VisaApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller {

	protected $visaService;

	public function __construct( VisaApplicationService $visaService ) {
		$this->visaService = $visaService;
	}

	public function getHomePage() {
		return view( 'pages.home' );
	}

	public function getTermsOfService() {
		return view( 'pages.terms-of-service' );
	}

	public function getPrivacyPolicy() {
		return view( 'pages.privacy-policy' );
	}

	public function getRefundPolicy() {
		$locale = app()->getLocale();
		$view = "pages.refund-policy-{$locale}";

		// Fallback to English if locale-specific file doesn't exist
		if ( ! view()->exists( $view ) ) {
			$view = 'pages.refund-policy-en';
		}

		return view( $view );
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

		// Get destination slug.
		$country_slugs = Countries::getCountrySlugs();
		$destination_slug = $country_slugs[ $destination ] ?? $destination;

		// Find or create draft application for the destination country.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( strtoupper( $destination ), $user );

		// Store nationality in the draft application.
		$application->nationality_country_code = strtoupper( $passport );
		$application->save();

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

		// Find or create draft application for this country
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Get nationality from draft application (convert to lowercase for comparison)
		$nationality = $application->nationality_country_code ? strtolower( $application->nationality_country_code ) : null;

		// If no nationality in draft, use locale-based default.
		if ( ! $nationality ) {
			// Locale to country code mapping.
			$locale_to_country = [
				'en' => 'us',
				'pt' => 'br',
				'es' => 'co',
			];

			$current_locale = app()->getLocale();
			$nationality = $locale_to_country[ $current_locale ] ?? 'us';
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
			'application' => $application,
		] );
	}

	public function postApplyPage( Request $request, $country ) {
		// Validate inputs.
		$valid_country_codes = implode( ',', Countries::getPriorityCountries() );
		$request->validate( [
			'nationality' => "required|string|in:{$valid_country_codes}",
			'applicants' => 'required|integer|min:1|max:10',
		] );

		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Find or create draft application
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Update application with form data
		$this->visaService->updateApplication( $application->id, [
			'nationality' => $request->input( 'nationality' ),
			'applicants' => $request->input( 'applicants' ),
		], 'apply' );

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

		// Find or create draft application
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// If no applicants set, redirect to apply page
		if ( ! $application->number_of_travelers ) {
			return redirect()->route( 'apply', [ 'country' => $country ] );
		}

		// Load existing travelers from database
		$existing_travelers = $application->travelers->keyBy( 'traveler_index' )->toArray();

		// Pricing and currency conversion.
		$base_price_usd = 49; // Base price per traveler in USD.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$total_price = $price_per_traveler * $application->number_of_travelers;
		$currency_symbol = Currencies::getSymbol( $user_currency );
		$currency_config = Currencies::getCurrencyConfig( $user_currency );

		return view( 'pages.application-details', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'applicants_count' => $application->number_of_travelers,
			'existing_travelers' => $existing_travelers,
			'price_per_traveler' => $price_per_traveler,
			'total_price' => $total_price,
			'currency_symbol' => $currency_symbol,
			'currency_config' => $currency_config,
			'application' => $application,
		] );
	}

	public function postApplicationDetails( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Find draft application
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

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

		// Save travelers to database
		$this->visaService->saveTravelers( $application->id, $submitted_travelers );

		// Update application with travelers info
		$this->visaService->updateApplication( $application->id, [
			'travelers' => $submitted_travelers,
		], 'application-details' );

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

		// Find or create draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// If no travelers, redirect to application details page.
		if ( ! $application->number_of_travelers ) {
			return redirect()->route( 'application.details', [ 'country' => $country ] );
		}

		// Load existing travelers from database.
		$existing_travelers = $application->travelers->keyBy( 'traveler_index' )->toArray();

		$applicants_count = $application->number_of_travelers;
		$nationality = $application->nationality_country_code ? strtolower( $application->nationality_country_code ) : null;

		// Pricing and currency conversion.
		$base_price_usd = 49; // Base price per traveler in USD.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$currency_symbol = Currencies::getSymbol( $user_currency );
		$currency_config = Currencies::getCurrencyConfig( $user_currency );

		return view( 'pages.passport-details', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'travelers' => $existing_travelers,
			'applicants_count' => $applicants_count,
			'nationality' => $nationality,
			'price_per_traveler' => $price_per_traveler,
			'currency_symbol' => $currency_symbol,
			'currency_config' => $currency_config,
			'application' => $application,
		] );
	}

	public function postPassportDetails( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

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

		// Find draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Load existing travelers from database.
		$existing_travelers = $application->travelers->keyBy( 'traveler_index' )->toArray();

		// Merge passport data with existing traveler data.
		$merged_travelers = [];
		foreach ( $submitted_travelers as $index => $passport_data ) {
			// Start with existing traveler data.
			$traveler_data = $existing_travelers[ $index ] ?? [];

			// Explicitly handle the add_passport_later checkbox.
			// If checkbox is not present in form data, it means it's unchecked.
			$passport_data['add_passport_later'] = isset( $passport_data['add_passport_later'] ) && $passport_data['add_passport_later'] == '1';

			// If passport is being added later, clear any existing passport data.
			if ( $passport_data['add_passport_later'] ) {
				$passport_data['passport_number'] = null;
				$passport_data['passport_expiration_month'] = null;
				$passport_data['passport_expiration_day'] = null;
				$passport_data['passport_expiration_year'] = null;
			}

			// Merge passport data into existing traveler data.
			$merged_travelers[ $index ] = array_merge( $traveler_data, $passport_data );
		}

		// Save travelers to database.
		$this->visaService->saveTravelers( $application->id, $merged_travelers );

		// Update application.
		$this->visaService->updateApplication( $application->id, [], 'passport-details' );

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

		// Find or create draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// If no travelers, redirect to passport details page.
		if ( ! $application->number_of_travelers ) {
			return redirect()->route( 'passport.details', [ 'country' => $country ] );
		}

		$applicants_count = $application->number_of_travelers;

		// Get pricing configuration for this country (use slug, not code).
		$pricing_config = config( "pricing.{$country}" );

		// Fallback to Colombia pricing if country not configured.
		if ( ! $pricing_config ) {
			$pricing_config = config( 'pricing.colombia' );
		}

		$base_price_usd = $pricing_config['base_form_price_usd'];
		$processing_options = $pricing_config['processing_options'];

		// Get selected processing option from application (default to standard).
		$selected_processing = $application->processing_option ?? 'standard';

		// Currency conversion.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$currency_symbol = Currencies::getSymbol( $user_currency );
		$currency_config = Currencies::getCurrencyConfig( $user_currency );

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
			'currency_config' => $currency_config,
			'processing_options' => $processing_options,
			'selected_processing' => $selected_processing,
			'application' => $application,
		] );
	}

	public function updateProcessingTime( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Validate processing option.
		$request->validate( [
			'processing_option' => 'required|string|in:standard,rush',
		] );

		$processing_option = $request->input( 'processing_option' );

		// Find draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Update application and recalculate pricing.
		$this->visaService->updateApplication( $application->id, [
			'processing_option' => $processing_option,
		], 'processing-time' );

		// Reload application to get updated pricing.
		$application->refresh();

		// Get user currency and convert processing fee.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$processing_fee = Currencies::convertFromUSD( $application->processing_fee_usd, $user_currency );

		// Return success with updated pricing.
		return response()->json( [
			'success' => true,
			'processing_fee' => $processing_fee,
		] );
	}

	public function postProcessingTime( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Validate processing option.
		$request->validate( [
			'processing_option' => 'required|string|in:standard,rush',
		] );

		$processing_option = $request->input( 'processing_option' );

		// Find draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Update application and recalculate pricing.
		$this->visaService->updateApplication( $application->id, [
			'processing_option' => $processing_option,
		], 'processing-time' );

		// Handle Ajax requests.
		if ( $request->ajax() || $request->wantsJson() ) {
			return response()->json( [
				'success' => true,
				'message' => __( 'Processing time saved!' ),
				'redirect' => route( 'review', [ 'country' => $country ] )
			] );
		}

		// Redirect to review page.
		return redirect()->route( 'review', [ 'country' => $country ] );
	}

	public function getReview( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );

		// Get country code from slug.
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Find or create draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// If no travelers or processing option, redirect to previous step.
		if ( ! $application->number_of_travelers || ! $application->processing_option ) {
			return redirect()->route( 'processing.time', [ 'country' => $country ] );
		}

		// Load travelers from database.
		$travelers = $application->travelers->keyBy( 'traveler_index' )->toArray();
		$applicants_count = $application->number_of_travelers;

		// Get pricing configuration for this country (use slug, not code).
		$pricing_config = config( "pricing.{$country}" );

		// Fallback to Colombia pricing if country not configured.
		if ( ! $pricing_config ) {
			$pricing_config = config( 'pricing.colombia' );
		}

		$base_price_usd = $pricing_config['base_form_price_usd'];
		$processing_options = $pricing_config['processing_options'];
		$visa_details = $pricing_config['visa_details'];
		$denial_protection = $pricing_config['denial_protection'];

		// Get selected processing option details.
		$processing_option = $application->processing_option;
		$selected_option = $processing_options[ $processing_option ];

		// Get denial protection status from application.
		$has_denial_protection = $application->has_denial_protection ?? false;

		// Currency conversion.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$price_per_traveler = Currencies::convertFromUSD( $base_price_usd, $user_currency );
		$currency_symbol = Currencies::getSymbol( $user_currency );
		$currency_config = Currencies::getCurrencyConfig( $user_currency );
		$processing_fee = Currencies::convertFromUSD( $application->processing_fee_usd, $user_currency );
		$denial_protection_price = Currencies::convertFromUSD( $denial_protection['price_usd'], $user_currency );

		// Get total price from application (already calculated).
		$total_price = Currencies::convertFromUSD( $application->total_amount_usd, $user_currency );

		// Calculate arrival date (today + processing days).
		$processing_days = $selected_option['days'];
		$arrival_date = now()->addDays( $processing_days );

		return view( 'pages.review', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country,
			'applicants_count' => $applicants_count,
			'travelers' => $travelers,
			'price_per_traveler' => $price_per_traveler,
			'currency_symbol' => $currency_symbol,
			'currency_config' => $currency_config,
			'processing_fee' => $processing_fee,
			'processing_option' => $processing_option,
			'processing_name' => $selected_option['name'],
			'processing_days' => $processing_days,
			'arrival_date' => $arrival_date,
			'visa_details' => $visa_details,
			'denial_protection' => $denial_protection,
			'denial_protection_price' => $denial_protection_price,
			'has_denial_protection' => $has_denial_protection,
			'total_price' => $total_price,
			'application' => $application,
		] );
	}

	public function updateDenialProtection( Request $request, $country ) {
		// Slug to country code mapping.
		$slug_to_code = array_flip( Countries::getCountrySlugs() );
		$country_code = $slug_to_code[ $country ] ?? null;

		if ( ! $country_code ) {
			abort( 404 );
		}

		// Validate denial protection choice.
		$request->validate( [
			'denial_protection' => 'required|boolean',
		] );

		$has_denial_protection = $request->input( 'denial_protection' );

		// Find draft application.
		$user = auth()->user();
		$application = $this->visaService->findOrCreateDraft( $country_code, $user );

		// Update application and recalculate pricing.
		$this->visaService->updateApplication( $application->id, [
			'denial_protection' => $has_denial_protection,
		], 'review' );

		// Reload application to get updated pricing.
		$application->refresh();

		// Get user currency and convert total.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$total_price = Currencies::convertFromUSD( $application->total_amount_usd, $user_currency );

		// Return success response with updated pricing.
		return response()->json( [
			'success' => true,
			'has_denial_protection' => $has_denial_protection,
			'total_price' => $total_price,
		] );
	}

	public function createPaymentIntent( Request $request, $country ) {
		try {
			// Slug to country code mapping.
			$slug_to_code = array_flip( Countries::getCountrySlugs() );
			$country_code = $slug_to_code[ $country ] ?? null;

			if ( ! $country_code ) {
				return response()->json( [
					'success' => false,
					'message' => __( 'Invalid country.' ),
				], 404 );
			}

			// Find draft application.
			$user = auth()->user();
			$application = $this->visaService->findOrCreateDraft( $country_code, $user );

			// Ensure application is complete.
			if ( ! $application->number_of_travelers || ! $application->processing_option ) {
				return response()->json( [
					'success' => false,
					'message' => __( 'Please complete all steps before payment.' ),
				], 400 );
			}

			// Create payment intent via service.
			$payment_data = $this->visaService->createPaymentIntent( $application->id );

			// Return client secret and publishable key.
			return response()->json( [
				'success' => true,
				'client_secret' => $payment_data['client_secret'],
				'public_key' => $payment_data['public_key'],
			] );
		} catch ( \Exception $e ) {
			\Log::error( 'Payment intent creation failed', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			] );

			return response()->json( [
				'success' => false,
				'message' => __( 'Failed to create payment. Please try again.' ),
				'error' => config( 'app.debug' ) ? $e->getMessage() : null,
			], 500 );
		}
	}

	public function viewOrder( Request $request, $order_number = null ) {
		// Get payment intent ID from query string (for Stripe redirects)
		$payment_intent_id = $request->query( 'payment_intent' );

		if ( ! $payment_intent_id && ! $order_number ) {
			return redirect()->route( 'home' )
				->with( 'error', __( 'Invalid payment session.' ) );
		}

		// Find application by payment intent ID or order number
		if ( $payment_intent_id ) {
			$application = \App\Models\VisaApplication::where( 'stripe_payment_intent_id', $payment_intent_id )
				->first();
		} elseif ( $order_number ) {
			// When viewing a specific order by order number, must be authenticated
			if ( ! auth()->check() ) {
				return redirect()->route( 'login' )
					->with( 'error', __( 'Please log in to view your orders.' ) );
			}
			$application = \App\Models\VisaApplication::where( 'order_number', $order_number )
				->where( 'user_id', auth()->id() )
				->first();
		}

		if ( ! $application ) {
			return redirect()->route( 'home' )
				->with( 'error', __( 'Payment session not found.' ) );
		}

		// Get country from application
		$country_slug = Countries::getCountrySlug( $application->destination_country_code );
		$country_code = $application->destination_country_code;

		// Only verify payment with Stripe if we have a payment intent ID (new payment)
		if ( $payment_intent_id ) {
			$stripe_secret = config( 'cashier.secret' );
			\Stripe\Stripe::setApiKey( $stripe_secret );

			try {
				$payment_intent = \Stripe\PaymentIntent::retrieve( $payment_intent_id );

				// Check if payment was successful.
				if ( $payment_intent->status === 'succeeded' ) {
					// Update application status to paid.
					$application->status = 'paid';
					$application->paid_at = now();
					$application->save();

					// Create or associate user account
					$this->createOrAssociateUser( $application );
				} else {
					// Payment not successful.
					return redirect()->route( 'apply', [ 'country' => $country_slug ] )
						->with( 'error', __( 'Payment was not successful. Please try again.' ) );
				}
			} catch ( \Exception $e ) {
				return redirect()->route( 'apply', [ 'country' => $country_slug ] )
					->with( 'error', __( 'Error verifying payment. Please contact support.' ) );
			}
		}

		// Load travelers for display.
		$application->load( 'travelers', 'primaryContact' );

		// Get all applications for this user (for order dropdown)
		$user_applications = [];
		if ( auth()->check() ) {
			$user_applications = \App\Models\VisaApplication::where( 'user_id', auth()->id() )
				->whereIn( 'status', [ 'paid', 'processing', 'approved', 'completed' ] )
				->orderBy( 'paid_at', 'desc' )
				->get();
		}

		// Get pricing configuration (use slug, not code).
		$pricing_config = config( "pricing.{$country_slug}" );
		if ( ! $pricing_config ) {
			$pricing_config = config( 'pricing.colombia' );
		}

		// Currency conversion for display.
		$user_currency = $request->cookie( 'preferred_currency', 'USD' );
		$currency_symbol = Currencies::getSymbol( $user_currency );
		$currency_config = Currencies::getCurrencyConfig( $user_currency );
		$total_amount = Currencies::convertFromUSD( $application->total_amount_usd, $user_currency );

		return view( 'pages.order', [
			'country_name' => Countries::getCountryName( $country_code ),
			'country_code' => $country_code,
			'country_slug' => $country_slug,
			'application' => $application,
			'user_applications' => $user_applications,
			'currency_symbol' => $currency_symbol,
			'currency_config' => $currency_config,
			'total_amount' => $total_amount,
			'pricing_config' => $pricing_config,
			'is_new_payment' => $payment_intent_id !== null,
		] );
	}

	/**
	 * Create or associate user account after successful payment.
	 *
	 * @param \App\Models\VisaApplication $application
	 * @return void
	 */
	protected function createOrAssociateUser( $application ) {
		// Get primary contact details
		$primary_contact = $application->primaryContact;

		if ( ! $primary_contact || ! $primary_contact->email ) {
			\Log::warning( 'Cannot create user account: No primary contact email', [
				'application_id' => $application->id,
			] );
			return;
		}

		$email = $primary_contact->email;
		$first_name = $primary_contact->first_name;
		$last_name = $primary_contact->last_name;
		$marketing_optin = $primary_contact->marketing_optin ?? false;

		// Check if user already exists
		$user = \App\Models\User::where( 'email', $email )->first();
		$is_new_user = false;

		if ( ! $user ) {
			// Create new user
			try {
				$user = \App\Models\User::create( [
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'locale' => $application->locale ?? app()->getLocale(),
					'email_notifications' => true,
					'marketing_optin' => $marketing_optin,
					'role' => 'user',
					// Note: password is intentionally NOT set - it will be null until user sets it via password reset link
				] );

				$is_new_user = true;

				\Log::info( 'New user account created after payment', [
					'user_id' => $user->id,
					'email' => $email,
					'application_id' => $application->id,
				] );
			} catch ( \Exception $e ) {
				\Log::error( 'Failed to create user account', [
					'email' => $email,
					'error' => $e->getMessage(),
					'application_id' => $application->id,
				] );
				return;
			}
		}

		// Associate application with user
		$application->user_id = $user->id;
		$application->save();

		// Only auto-login NEW users
		if ( $is_new_user && ! auth()->check() ) {
			auth()->login( $user );

			// Send welcome email with password reset link
			try {
				$user->notify( new \App\Notifications\WelcomeUserWithPasswordReset( $application ) );
			} catch ( \Exception $e ) {
				\Log::error( 'Failed to send welcome email', [
					'user_id' => $user->id,
					'error' => $e->getMessage(),
				] );
			}
		}
	}

	/**
	 * Show user orders page with all orders.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function getOrders( Request $request ) {
		$user = auth()->user();

		// Get all visa applications for this user, excluding drafts, ordered by most recent first
		$applications = \App\Models\VisaApplication::where( 'user_id', $user->id )
			->where( 'status', '!=', 'draft' )
			->orderBy( 'created_at', 'desc' )
			->get();

		return view( 'pages.orders', [
			'user' => $user,
			'applications' => $applications,
		] );
	}
}

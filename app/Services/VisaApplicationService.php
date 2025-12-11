<?php

namespace App\Services;

use App\Models\VisaApplication;
use App\Models\Traveler;
use App\Helpers\Currencies;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VisaApplicationService {
	/**
	 * Find or create a draft application for a specific country.
	 *
	 * @param string $country Country code (e.g., 'co', 'br')
	 * @param \App\Models\User|null $user Authenticated user or null for guests
	 * @return \App\Models\VisaApplication
	 */
	public function findOrCreateDraft( $country, $user = null ) {
		$query = VisaApplication::where( 'destination_country_code', strtoupper( $country ) )
			->whereIn( 'status', ['draft', 'pending_payment'] );

		if ( $user ) {
			// Authenticated user
			$query->where( 'user_id', $user->id );
		} else {
			// Guest user - use session ID
			$session_id = session()->getId();
			$query->where( 'session_id', $session_id )
				->whereNull( 'user_id' );
		}

		$application = $query->first();

		// If no draft exists, create a new one
		if ( ! $application ) {
			$application = new VisaApplication();
			$application->destination_country_code = strtoupper( $country );
			$application->status                   = 'draft';
			$application->locale                   = app()->getLocale();

			if ( $user ) {
				$application->user_id = $user->id;
			} else {
				$application->session_id = session()->getId();
			}

			// Set default values
			$application->number_of_travelers      = 1;
			$application->processing_option        = 'standard';
			$application->has_denial_protection    = false;
			$application->currency_code            = request()->cookie( 'preferred_currency', 'USD' );
			$application->base_price_usd           = 0;
			$application->processing_fee_usd       = 0;
			$application->denial_protection_fee_usd = 0;
			$application->total_amount_usd         = 0;
			$application->total_amount_local       = 0;
			$application->exchange_rate            = 1;
			$application->primary_contact_email    = '';
			$application->nationality_country_code = '';

			$application->save();
		}

		return $application;
	}

	/**
	 * Update a visa application at a specific step.
	 *
	 * @param int $application_id Application ID
	 * @param array $data Form data
	 * @param string $step Step name ('apply', 'application-details', 'passport-details', 'processing-time', 'review')
	 * @return \App\Models\VisaApplication
	 */
	public function updateApplication( $application_id, $data, $step ) {
		$application = VisaApplication::findOrFail( $application_id );

		switch ( $step ) {
			case 'apply':
				// Step 1: nationality and number of applicants
				$application->nationality_country_code = $data['nationality'] ?? $application->nationality_country_code;
				$application->number_of_travelers      = $data['applicants'] ?? $application->number_of_travelers;
				break;

			case 'application-details':
				// Step 2: travelers' personal details (handled separately via saveTravelers)
				// Update number of travelers based on actual count
				if ( isset( $data['travelers'] ) ) {
					$application->number_of_travelers = count( $data['travelers'] );
					// Get primary contact email from first traveler
					$first_traveler = $data['travelers'][0] ?? $data['travelers'][array_key_first( $data['travelers'] )] ?? null;
					if ( $first_traveler && isset( $first_traveler['email'] ) ) {
						$application->primary_contact_email = $first_traveler['email'];
					}
				}
				break;

			case 'passport-details':
				// Step 3: passport details (handled separately via saveTravelers)
				// Nothing to update on application level
				break;

			case 'processing-time':
				// Step 4: processing option
				$application->processing_option = $data['processing_option'] ?? $application->processing_option;
				// Recalculate pricing after processing option changes
				$this->calculatePricing( $application );
				break;

			case 'review':
				// Step 5: denial protection
				$application->has_denial_protection = $data['denial_protection'] ?? false;
				// Recalculate pricing after denial protection changes
				$this->calculatePricing( $application );
				break;
		}

		$application->save();

		return $application;
	}

	/**
	 * Save travelers for an application.
	 *
	 * @param int $application_id Application ID
	 * @param array $travelers_data Array of traveler data
	 * @return void
	 */
	public function saveTravelers( $application_id, $travelers_data ) {
		$application = VisaApplication::findOrFail( $application_id );

		// Delete existing travelers for this application
		Traveler::where( 'visa_application_id', $application_id )->delete();

		// Create fresh travelers from the data
		foreach ( $travelers_data as $traveler_index => $traveler_data ) {
			$traveler = new Traveler();
			$traveler->visa_application_id = $application_id;
			$traveler->traveler_index      = $traveler_index; // Use the actual key from form data (1-based)
			$traveler->is_primary_contact  = ( $traveler_index === 1 ); // First traveler is primary

			// Personal information
			$traveler->first_name = $traveler_data['first_name'] ?? '';
			$traveler->last_name  = $traveler_data['last_name'] ?? '';
			$traveler->email      = $traveler_data['email'] ?? null;

			// Parse date of birth
			if ( isset( $traveler_data['date_of_birth_year'], $traveler_data['date_of_birth_month'], $traveler_data['date_of_birth_day'] ) ) {
				try {
					$traveler->date_of_birth = Carbon::create(
						$traveler_data['date_of_birth_year'],
						$traveler_data['date_of_birth_month'],
						$traveler_data['date_of_birth_day']
					);
				} catch ( \Exception $e ) {
					// Invalid date, leave null
					$traveler->date_of_birth = null;
				}
			}

			// Passport information
			$traveler->nationality_country_code = $traveler_data['nationality'] ?? $application->nationality_country_code;
			$traveler->passport_number          = $traveler_data['passport_number'] ?? null;
			$traveler->add_passport_later       = $traveler_data['add_passport_later'] ?? false;

			// Parse passport expiration date
			if ( ! $traveler->add_passport_later && isset( $traveler_data['passport_expiration_year'], $traveler_data['passport_expiration_month'], $traveler_data['passport_expiration_day'] ) ) {
				try {
					$traveler->passport_expiration_date = Carbon::create(
						$traveler_data['passport_expiration_year'],
						$traveler_data['passport_expiration_month'],
						$traveler_data['passport_expiration_day']
					);
				} catch ( \Exception $e ) {
					// Invalid date, leave null
					$traveler->passport_expiration_date = null;
				}
			}

			// Store marketing_optin in additional_data for primary contact
			if ( $traveler_index === 1 && isset( $traveler_data['marketing_optin'] ) ) {
				$traveler->additional_data = [
					'marketing_optin' => (bool) $traveler_data['marketing_optin']
				];
			}

			$traveler->save();
		}
	}

	/**
	 * Calculate pricing for an application.
	 *
	 * @param \App\Models\VisaApplication $application
	 * @return void
	 */
	public function calculatePricing( $application ) {
		// Get pricing config for the destination country
		// Convert country code to slug (e.g., 'CO' -> 'colombia')
		$country_slugs = \App\Helpers\Countries::getCountrySlugs();
		$country_slug = $country_slugs[ $application->destination_country_code ] ?? strtolower( $application->destination_country_code );

		$pricing = config( "pricing.{$country_slug}" );

		if ( ! $pricing ) {
			// Fallback if country not configured
			$pricing = config( 'pricing.colombia' ); // Default to Colombia pricing
		}

		// Base price: price per traveler × number of travelers
		$base_price_usd = $pricing['base_form_price_usd'] * $application->number_of_travelers;

		// Processing fee (flat fee, not per traveler)
		$processing_option  = $application->processing_option ?? 'standard';
		$processing_fee_usd = $pricing['processing_options'][ $processing_option ]['price_usd'] ?? 0;

		// Denial protection (flat fee, not per traveler)
		$denial_protection_fee_usd = 0;
		if ( $application->has_denial_protection ) {
			$denial_protection_fee_usd = $pricing['denial_protection']['price_usd'] ?? 0;
		}

		// Total in USD
		$total_amount_usd = $base_price_usd + $processing_fee_usd + $denial_protection_fee_usd;

		// Get exchange rate
		$currency_code  = $application->currency_code ?? 'USD';
		$exchange_rate  = 1;
		$total_amount_local = $total_amount_usd;

		if ( $currency_code !== 'USD' ) {
			$exchange_rate      = Currencies::getExchangeRate( $currency_code );
			$total_amount_local = $total_amount_usd * $exchange_rate;
		}

		// Update application with calculated pricing
		$application->base_price_usd            = $base_price_usd;
		$application->processing_fee_usd        = $processing_fee_usd;
		$application->denial_protection_fee_usd = $denial_protection_fee_usd;
		$application->total_amount_usd          = $total_amount_usd;
		$application->exchange_rate             = $exchange_rate;
		$application->total_amount_local        = $total_amount_local;

		// Calculate expected completion date
		$processing_days = $pricing['processing_options'][ $processing_option ]['days'] ?? 3;
		$application->expected_completion_date = now()->addDays( $processing_days )->toDateString();

		$application->save();
	}

	/**
	 * Create a Stripe Payment Intent for an application.
	 *
	 * @param int $application_id Application ID
	 * @return array Array with 'client_secret' and 'public_key'
	 */
	public function createPaymentIntent( $application_id ) {
		$application = VisaApplication::with( 'travelers' )->findOrFail( $application_id );

		// Recalculate pricing to ensure it's up to date
		$this->calculatePricing( $application );
		$application->refresh();

		// Store submitted timestamp but keep status as 'draft'
		// Status will change to 'paid' when payment succeeds
		$application->submitted_at = now();
		$application->save();

		// Get Stripe secret key
		$stripe_secret = config( 'cashier.secret' );

		if ( ! $stripe_secret ) {
			throw new \Exception( 'Stripe secret key not configured. Please add STRIPE_SECRET to your .env file.' );
		}

		\Stripe\Stripe::setApiKey( $stripe_secret );

		// Calculate amount in smallest currency unit
		$currency_code = strtolower( $application->currency_code );
		$amount_local  = $application->total_amount_local;

		// Convert to smallest unit (cents for USD/BRL, pesos for COP which has no decimals)
		$currency_config = Currencies::getCurrencyConfig( $application->currency_code );
		$decimal_places  = $currency_config['decimal_places'];

		if ( $decimal_places > 0 ) {
			// Has decimals (USD, BRL) - convert to cents
			$amount = (int) round( $amount_local * 100 );
		} else {
			// No decimals (COP) - amount is already in smallest unit
			$amount = (int) round( $amount_local );
		}

		// Create Payment Intent
		try {
			$payment_intent = \Stripe\PaymentIntent::create( [
				'amount'      => $amount,
				'currency'    => $currency_code,
				'description' => "Visa Application - {$application->order_number}",
				'metadata'    => [
					'visa_application_id' => $application->id,
					'order_number'        => $application->order_number,
				],
			] );
		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			throw new \Exception( 'Stripe API error: ' . $e->getMessage() );
		}

		// Store payment intent ID
		$application->stripe_payment_intent_id = $payment_intent->id;
		$application->save();

		return [
			'client_secret' => $payment_intent->client_secret,
			'public_key'    => config( 'cashier.key' ),
		];
	}
}

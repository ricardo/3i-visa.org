<?php

namespace App\Http\Controllers;

use App\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller {

	/**
	 * Handle Stripe webhook events.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function handleWebhook( Request $request ) {
		// Get the webhook secret from config.
		$webhook_secret = config( 'cashier.webhook_secret' );

		// Verify webhook signature if secret is configured.
		if ( $webhook_secret ) {
			$signature = $request->header( 'Stripe-Signature' );

			try {
				$event = \Stripe\Webhook::constructEvent(
					$request->getContent(),
					$signature,
					$webhook_secret
				);
			} catch ( \UnexpectedValueException $e ) {
				// Invalid payload.
				Log::error( 'Stripe webhook: Invalid payload', [ 'error' => $e->getMessage() ] );
				return response()->json( [ 'error' => 'Invalid payload' ], 400 );
			} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
				// Invalid signature.
				Log::error( 'Stripe webhook: Invalid signature', [ 'error' => $e->getMessage() ] );
				return response()->json( [ 'error' => 'Invalid signature' ], 400 );
			}
		} else {
			// No webhook secret configured, use the payload directly (not recommended for production).
			$event = json_decode( $request->getContent(), true );
		}

		// Handle the event.
		$type = $event['type'] ?? null;
		$data = $event['data']['object'] ?? null;

		Log::info( 'Stripe webhook received', [
			'type' => $type,
			'payment_intent_id' => $data['id'] ?? null,
		] );

		switch ( $type ) {
			case 'payment_intent.succeeded':
				$this->handlePaymentIntentSucceeded( $data );
				break;

			case 'payment_intent.payment_failed':
				$this->handlePaymentIntentFailed( $data );
				break;

			case 'charge.refunded':
				$this->handleChargeRefunded( $data );
				break;

			default:
				Log::info( 'Stripe webhook: Unhandled event type', [ 'type' => $type ] );
		}

		// Return a 200 response to acknowledge receipt of the event.
		return response()->json( [ 'success' => true ] );
	}

	/**
	 * Handle successful payment intent.
	 *
	 * @param array $payment_intent Payment intent data from Stripe
	 * @return void
	 */
	protected function handlePaymentIntentSucceeded( $payment_intent ) {
		$payment_intent_id = $payment_intent['id'] ?? null;

		if ( ! $payment_intent_id ) {
			Log::error( 'Stripe webhook: Payment intent ID not found in payment_intent.succeeded event' );
			return;
		}

		// Find the application by payment intent ID.
		$application = VisaApplication::where( 'stripe_payment_intent_id', $payment_intent_id )->first();

		if ( ! $application ) {
			Log::error( 'Stripe webhook: Application not found for payment intent', [
				'payment_intent_id' => $payment_intent_id,
			] );
			return;
		}

		// Update application status to paid if not already.
		if ( $application->status !== 'paid' ) {
			$application->status = 'paid';
			$application->paid_at = now();
			$application->save();

			Log::info( 'Stripe webhook: Application marked as paid', [
				'application_id' => $application->id,
				'order_number' => $application->order_number,
				'payment_intent_id' => $payment_intent_id,
			] );

			// Create or associate user account
			$this->createOrAssociateUser( $application );
		} else {
			Log::info( 'Stripe webhook: Application already marked as paid', [
				'application_id' => $application->id,
				'payment_intent_id' => $payment_intent_id,
			] );
		}
	}

	/**
	 * Create or associate user account after successful payment.
	 *
	 * @param \App\Models\VisaApplication $application
	 * @return void
	 */
	protected function createOrAssociateUser( $application ) {
		// Load primary contact if not already loaded
		if ( ! $application->relationLoaded( 'primaryContact' ) ) {
			$application->load( 'primaryContact' );
		}

		$primary_contact = $application->primaryContact;

		if ( ! $primary_contact || ! $primary_contact->email ) {
			Log::warning( 'Stripe webhook: Cannot create user account - No primary contact email', [
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

				Log::info( 'Stripe webhook: New user account created', [
					'user_id' => $user->id,
					'email' => $email,
					'application_id' => $application->id,
				] );
			} catch ( \Exception $e ) {
				Log::error( 'Stripe webhook: Failed to create user account', [
					'email' => $email,
					'error' => $e->getMessage(),
					'application_id' => $application->id,
				] );
				return;
			}
		}

		// Associate application with user if not already associated
		if ( ! $application->user_id ) {
			$application->user_id = $user->id;
			$application->save();

			Log::info( 'Stripe webhook: Application associated with user', [
				'user_id' => $user->id,
				'application_id' => $application->id,
			] );
		}

		// Send welcome email for NEW users only
		if ( $is_new_user ) {
			try {
				$user->notify( new \App\Notifications\WelcomeUserWithPasswordReset( $application ) );
				Log::info( 'Stripe webhook: Welcome email sent to new user', [
					'user_id' => $user->id,
				] );
			} catch ( \Exception $e ) {
				Log::error( 'Stripe webhook: Failed to send welcome email', [
					'user_id' => $user->id,
					'error' => $e->getMessage(),
				] );
			}
		}
	}

	/**
	 * Handle failed payment intent.
	 *
	 * @param array $payment_intent Payment intent data from Stripe
	 * @return void
	 */
	protected function handlePaymentIntentFailed( $payment_intent ) {
		$payment_intent_id = $payment_intent['id'] ?? null;

		if ( ! $payment_intent_id ) {
			Log::error( 'Stripe webhook: Payment intent ID not found in payment_intent.payment_failed event' );
			return;
		}

		// Find the application by payment intent ID.
		$application = VisaApplication::where( 'stripe_payment_intent_id', $payment_intent_id )->first();

		if ( ! $application ) {
			Log::error( 'Stripe webhook: Application not found for failed payment', [
				'payment_intent_id' => $payment_intent_id,
			] );
			return;
		}

		// Update application status to payment_failed.
		$application->status = 'payment_failed';
		$application->save();

		Log::info( 'Stripe webhook: Payment failed for application', [
			'application_id' => $application->id,
			'order_number' => $application->order_number,
			'payment_intent_id' => $payment_intent_id,
		] );

		// TODO: Send payment failed email to customer
		// TODO: Notify admin of failed payment
	}

	/**
	 * Handle refunded charge.
	 *
	 * @param array $charge Charge data from Stripe
	 * @return void
	 */
	protected function handleChargeRefunded( $charge ) {
		$payment_intent_id = $charge['payment_intent'] ?? null;

		if ( ! $payment_intent_id ) {
			Log::error( 'Stripe webhook: Payment intent ID not found in charge.refunded event' );
			return;
		}

		// Find the application by payment intent ID.
		$application = VisaApplication::where( 'stripe_payment_intent_id', $payment_intent_id )->first();

		if ( ! $application ) {
			Log::error( 'Stripe webhook: Application not found for refunded charge', [
				'payment_intent_id' => $payment_intent_id,
			] );
			return;
		}

		// Update application status to refunded.
		$application->status = 'refunded';
		$application->save();

		Log::info( 'Stripe webhook: Charge refunded for application', [
			'application_id' => $application->id,
			'order_number' => $application->order_number,
			'payment_intent_id' => $payment_intent_id,
		] );

		// TODO: Send refund confirmation email to customer
		// TODO: Notify admin of refunded application
	}
}

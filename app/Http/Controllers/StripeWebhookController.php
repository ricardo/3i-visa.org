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

			// TODO: Send confirmation email to customer
			// TODO: Notify admin of new paid application
		} else {
			Log::info( 'Stripe webhook: Application already marked as paid', [
				'application_id' => $application->id,
				'payment_intent_id' => $payment_intent_id,
			] );
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

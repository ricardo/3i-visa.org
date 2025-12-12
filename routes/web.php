<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

use App\Http\Controllers\SiteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StripeWebhookController;

use Livewire\Livewire;

// Stripe webhook (outside locale group, no CSRF protection needed)
Route::post( 'stripe/webhook', [ StripeWebhookController::class, 'handleWebhook' ] )->name( 'stripe.webhook' );

$locale = Request::segment( 1 );

if ( $locale === 'en' || ! in_array( $locale, array_keys( config( 'app.supported_locales' ) ) ) ) {
	$locale = '';
}

Route::group( [
	'prefix' => $locale,
], function () {

	Route::get( '/', [ SiteController::class, 'getHomePage' ] )->name( 'home' );

	// Demo page for searchable input component.
	Route::get( 'demo/searchable-input', function() {
		return view( 'examples.searchable-input-demo' );
	} )->name( 'demo.searchable-input' );

	Route::get( 'contact', [ SiteController::class, 'getContactPage' ] )->name( 'contact' );
	Route::post( 'contact', [ SiteController::class, 'postContactForm' ] )->name( 'contact' );
	Route::get( 'terms-of-service', [ SiteController::class, 'getTermsOfService' ] )->name( 'terms-of-service' );
	Route::get( 'privacy-policy', [ SiteController::class, 'getPrivacyPolicy' ] )->name( 'privacy-policy' );
	Route::get( 'refund-policy', [ SiteController::class, 'getRefundPolicy' ] )->name( 'refund-policy' );

	// Language switcher.
	Route::post( 'language/switch', [ SiteController::class, 'postSwitchLanguage' ] )->name( 'language.switch' );
	Route::post( 'preferences/save', [ SiteController::class, 'postSavePreferences' ] )->name( 'preferences.save' );

	// Visa check.
	Route::post( 'visa-check', [ SiteController::class, 'postVisaCheck' ] )->name( 'visa-check' );

	// Apply page.
	Route::get( '{country}/apply', [ SiteController::class, 'getApplyPage' ] )->name( 'apply' );
	Route::post( '{country}/apply', [ SiteController::class, 'postApplyPage' ] )->name( 'apply.submit' );

	// Application details page.
	Route::get( '{country}/application-details', [ SiteController::class, 'getApplicationDetails' ] )->name( 'application.details' );
	Route::post( '{country}/application-details', [ SiteController::class, 'postApplicationDetails' ] )->name( 'application.details.submit' );

	// Passport details page.
	Route::get( '{country}/passport-details', [ SiteController::class, 'getPassportDetails' ] )->name( 'passport.details' );
	Route::post( '{country}/passport-details', [ SiteController::class, 'postPassportDetails' ] )->name( 'passport.details.submit' );

	// Processing time page.
	Route::get( '{country}/processing-time', [ SiteController::class, 'getProcessingTime' ] )->name( 'processing.time' );
	Route::post( '{country}/processing-time', [ SiteController::class, 'postProcessingTime' ] )->name( 'processing.time.submit' );
	Route::post( '{country}/processing-time/update', [ SiteController::class, 'updateProcessingTime' ] )->name( 'processing.time.update' );

	// Review page.
	Route::get( '{country}/review', [ SiteController::class, 'getReview' ] )->name( 'review' );
	Route::post( '{country}/review/update-denial-protection', [ SiteController::class, 'updateDenialProtection' ] )->name( 'review.update.denial' );

	// Payment routes.
	Route::post( '{country}/payment/create-intent', [ SiteController::class, 'createPaymentIntent' ] )->name( 'payment.intent' );
	Route::get( 'payment/success', [ SiteController::class, 'paymentSuccess' ] )->name( 'payment.success' );

	// Auth:Login.
	Route::get( 'login', [ AuthController::class, 'getLogin' ] )->name( 'login' );
	Route::post( 'login/check-email', [ AuthController::class, 'checkEmail' ] )->name( 'login.check-email' );
	Route::post( 'login', [ AuthController::class, 'postLogin' ] )->name( 'login.post' );

	// Auth:Signup.
	Route::post( 'signup/email', [ AuthController::class, 'postSignup' ] )->name( 'signup.email' );
	Route::get( 'signup/verify', [ AuthController::class, 'getSignupVerifyEmail' ] )->name( 'signup.verify' );

	// Auth:ResetPassword.
	Route::post( 'password/request', [ AuthController::class, 'postPasswordRequest' ] )->name( 'password.email' );
	Route::get( 'password/reset/{token?}', [ AuthController::class, 'getPasswordResetPage' ] )->name( 'password.reset' );
	Route::post( 'password/reset', [ AuthController::class, 'postPasswordReset' ] )->name( 'password.update' );

	// Auth:Logout.
	Route::get( 'logout', [ AuthController::class, 'logout' ] )->name( 'logout' );

	// Auth:Account.
	Route::get( 'account', [ SiteController::class, 'getAccount' ] )->name( 'account' )->middleware( 'auth' );

	Livewire::setUpdateRoute( function ( $handle ) {
        return Route::post( 'livewire/update', $handle );
    } );
} );

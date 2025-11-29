<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

use App\Http\Controllers\SiteController;
use App\Http\Controllers\AuthController;

$locale = Request::segment( 1 );

if ( $locale === 'en' || ! in_array( $locale, array_keys( config( 'app.supported_locales' ) ) ) ) {
	$locale = '';
}

Route::group( [
	'prefix' => $locale,
], function () {

	Route::get( '/', [ SiteController::class, 'getHomePage' ] )->name( 'home' );

	Route::get( 'contact', [ SiteController::class, 'getContactPage' ] )->name( 'contact' );
	Route::post( 'contact', [ SiteController::class, 'postContactForm' ] )->name( 'contact' );
	Route::get( 'terms', [ SiteController::class, 'getTerms' ] )->name( 'terms' );
	Route::get( 'privacy', [ SiteController::class, 'getPrivacy' ] )->name( 'privacy' );

	// Language switcher.
	Route::post( 'language/switch', [ SiteController::class, 'postSwitchLanguage' ] )->name( 'language.switch' );

	// Auth:Login.
	Route::post( 'login', [ AuthController::class, 'postLogin' ] )->name( 'login.post' );
	Route::get( 'login/google', [ AuthController::class, 'getLoginWithGoogle' ] )->name( 'login.google' );
	Route::get( 'login/google/callback', [ AuthController::class, 'handleGoogleCallback' ] );

	// Auth:Signup.
	Route::post( 'signup/email', [ AuthController::class, 'postSignup' ] )->name( 'signup.email' );
	Route::get( 'signup/verify', [ AuthController::class, 'getSignupVerifyEmail' ] )->name( 'signup.verify' );
	Route::get( 'signup/google', [ AuthController::class, 'getSignupWithGoogle' ] )->name( 'signup.google' );

	// Auth:ResetPassword.
	Route::post( 'password/request', [ AuthController::class, 'postPasswordRequest' ] )->name( 'password.email' );
	Route::get( 'password/reset/{token?}', [ AuthController::class, 'getPasswordResetPage' ] )->name( 'password.reset' );
	Route::post( 'password/reset', [ AuthController::class, 'postPasswordReset' ] )->name( 'password.reset.post' );

	// Auth:Logout.
	Route::get( 'logout', [ AuthController::class, 'logout' ] )->name( 'logout' );

} );
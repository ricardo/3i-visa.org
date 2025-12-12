<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller {
	/**
	 * Show the login page.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function getLogin( Request $request ) {
		// If already authenticated, redirect to home
		if ( auth()->check() ) {
			return redirect()->route( 'home' );
		}

		return view( 'pages.login' );
	}

	/**
	 * Check email and determine if user has set password.
	 * Used for 2-step login flow.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function checkEmail( Request $request ) {
		$request->validate( [
			'email' => 'required|email',
		] );

		$user = \App\Models\User::where( 'email', $request->email )->first();

		if ( ! $user ) {
			return response()->json( [
				'status' => 'error',
				'message' => __( 'No account found with this email address.' ),
			], 404 );
		}

		// Check if user has set a password
		if ( is_null( $user->password ) ) {
			// User hasn't set password yet - resend password reset email
			try {
				$user->notify( new \App\Notifications\WelcomeUserWithPasswordReset( $user->visaApplications()->first() ) );

				return response()->json( [
					'status' => 'no_password',
					'message' => __( 'Check your email! We\'ve sent you a link to set your password.' ),
				] );
			} catch ( \Exception $e ) {
				\Log::error( 'Failed to send password reset email', [
					'user_id' => $user->id,
					'error' => $e->getMessage(),
				] );

				return response()->json( [
					'status' => 'error',
					'message' => __( 'An unexpected error occurred.' ),
				], 500 );
			}
		}

		// User has password - proceed to step 2
		return response()->json( [
			'status' => 'has_password',
			'message' => __( 'Please enter your password to continue.' ),
		] );
	}

	/**
	 * Handle login form submission.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postLogin( Request $request ) {
		$request->validate( [
			'email' => 'required|email',
			'password' => 'required|string',
		] );

		// Attempt to authenticate user with remember me always on
		if ( auth()->attempt( [
			'email' => $request->email,
			'password' => $request->password,
		], true ) ) {
			// Authentication successful
			$request->session()->regenerate();

			return redirect()->intended( route( 'account' ) );
		}

		// Authentication failed
		return redirect()->route( 'login' )
			->withInput( $request->only( 'email' ) )
			->with( 'error', __( 'Invalid email or password.' ) );
	}

	/**
	 * Handle signup form submission.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postSignup( Request $request ) {
		// TODO: Implement signup logic
		return redirect()->route( 'login' )->with( 'error', __( 'Signup functionality coming soon.' ) );
	}

	/**
	 * Handle email verification.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function getSignupVerifyEmail( Request $request ) {
		// TODO: Implement email verification
		return redirect()->route( 'home' )->with( 'success', __( 'Email verified.' ) );
	}

	/**
	 * Handle password reset request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postPasswordRequest( Request $request ) {
		$request->validate( [
			'email' => 'required|email',
		] );

		// Find user by email
		$user = \App\Models\User::where( 'email', $request->email )->first();

		if ( ! $user ) {
			return redirect()->back()
				->withInput( $request->only( 'email' ) )
				->with( 'error', __( 'No account found with this email address.' ) );
		}

		// Generate password reset token
		$token = \Illuminate\Support\Facades\Password::broker()->createToken( $user );

		// Send password reset notification
		try {
			$user->notify( new \App\Notifications\ResetPasswordNotification( $token ) );

			return redirect()->route( 'login' )->with( 'success', __( 'Password reset link sent to your email.' ) );
		} catch ( \Exception $e ) {
			\Log::error( 'Failed to send password reset email', [
				'user_id' => $user->id,
				'error' => $e->getMessage(),
			] );

			return redirect()->back()
				->withInput( $request->only( 'email' ) )
				->with( 'error', __( 'An unexpected error occurred.' ) );
		}
	}

	/**
	 * Show password reset page.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param string|null $token
	 * @return \Illuminate\View\View
	 */
	public function getPasswordResetPage( Request $request, $token = null ) {
		return view( 'pages.password-reset', [
			'token' => $token,
			'email' => $request->query( 'email' ),
		] );
	}

	/**
	 * Handle password reset form submission.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postPasswordReset( Request $request ) {
		$request->validate( [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|string|min:8|confirmed',
		] );

		$status = \Illuminate\Support\Facades\Password::reset(
			$request->only( 'email', 'password', 'password_confirmation', 'token' ),
			function ( $user, $password ) {
				$user->password = $password;
				$user->save();

				// Auto-login the user after setting password
				auth()->login( $user, true );
			}
		);

		if ( $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET ) {
			return redirect()->route( 'account' )->with( 'success', __( 'Password has been set. You are now logged in!' ) );
		}

		return redirect()->back()
			->withInput( $request->only( 'email' ) )
			->with( 'error', __( $status ) );
	}

	/**
	 * Handle logout.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function logout( Request $request ) {
		auth()->logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect()->route( 'home' );
	}
}

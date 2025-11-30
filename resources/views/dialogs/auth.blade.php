<dialog id="dialog-auth" class="dialog" @if ( request( 'reset' ) || ! empty( $open ) ) open @endif aria-expanded="false" aria-label="Login or Sign Up">
	<article id="login" class="small draggable" @if ( ! request( 'reset' ) && empty( $open ) ) style="display: none;" @endif>
		<form id="login-form">
			<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
			<div class="handle"></div>

			<h2>@lang( 'Log in to your account' )</h2>

			<p style="margin-bottom: 2rem">@lang( 'Don\'t have an account?' ) <a class="toggle-signup">@lang( 'Sign up here' )</a>.</p>

			<a href="{{ route( 'login.google' ) }}" role="button" class="vendor-login-button contrast flex busy">
				@include( 'icons.google' )
				@lang( 'Log in with Google' )
			</a>

			<div class="or-separator">
				<span>@lang( 'OR' )</span>
			</div>

			<input type="email" name="email" placeholder="@lang( 'Email' )" autocomplete="email" required>
			<small class="invalid-helper"></small>

			<input type="password" id="password" name="password" placeholder="@lang( 'Password' )" required>
			<small class="invalid-helper"></small>

			<div class="grid" style="margin-bottom: 1.5rem">
				<div style="text-align: right">
					<a id="toggle-forgot-password" class="color-text fs-2">@lang( 'Forgot your password?' )</a>
				</div>
			</div>

			<button type="submit" class="mb-0">@lang( 'Log in' )</button>
		</form>
	</article>

	<article id="signup" class="small draggable" style="display: none">
		<form id="signup-form">
			<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
			<div class="handle"></div>

			<h2>
				@if ( request()->routeIs( 'create' ) )
					@lang( 'She is waiting for you, sign up!' )
				@else
					@lang( 'Create a Free Account' )
				@endif
			</h2>

			<p style="margin-bottom: 2rem">@lang( 'Already have an account?' ) <a class="toggle-login">@lang( 'Log in here' )</a>.</p>

			<a href="{{ route( 'login.google' ) }}" role="button" class="vendor-login-button contrast flex busy">
				@include( 'icons.google' )
				@lang( 'Continue with Google' )
			</a>

			<div class="or-separator">
				<span>@lang( 'OR' )</span>
			</div>

			<input type="email" name="email" placeholder="@lang( 'Email' )" autocomplete="email" required>
			<small class="invalid-helper"></small>

			<input type="password" name="password" placeholder="@lang( 'Password (min 6 characters)' )" autocomplete="new-password" required>
			<small class="invalid-helper"></small>

			<input type="password" name="password_confirmation" placeholder="@lang( 'Confirm Password' )" autocomplete="new-password" required>
			<small class="invalid-helper"></small>

			<button type="submit">@lang( 'Sign Up' )</button>

			<p class="mt-4 mb-0 fs-0 color-muted">
				@lang( 'By signing up, you confirm you are at least 18 years of age, and agree to our' ) <a href="{{ route( 'terms' ) }}" target="_blank" class="color-muted">@lang( 'Terms of Service' )</a> @lang( 'and' ) <a href="{{ route( 'privacy' ) }}" target="_blank" class="color-muted">@lang( 'Privacy Policy' )</a>.
			</p>
		</form>
	</article>

	<article id="forgot-password" class="small draggable" style="display: none;">
		<form id="forgot-password-form">
			<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
			<div class="handle"></div>

			<h2>@lang( 'Reset your password' )</h2>

			<p style="margin-bottom: 2rem; text-align: center; text-wrap: stable;">
				@lang( 'Enter your email address and we\'ll send you a link to reset your password.' )
			</p>

			<input type="email" name="email" placeholder="@lang( 'Email' )" autocomplete="email" required>
			<small class="invalid-helper"></small>

			<div class="grid mt-3 mb-3">
				<div style="text-align: left">
					<a class="toggle-login color-text">@lang( 'Back to login' )</a>
				</div>
			</div>

			<button type="submit" class="mt-2">@lang( 'Send Reset Link' )</button>
		</form>
	</article>

	<article id="reset-email-sent" class="small draggable" style="display: none;">
		<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
		<div class="handle"></div>

		<h2>@lang( 'Check your inbox' )</h2>

		<div class="icon-big aos fade-in-left" data-animation="fade-in-left" data-delay=".5s" style="animation-duration: 0.3s; animation-delay: 0.5s;">
			@include( 'icons.mail-send' )
		</div>

		<p style="margin: 2rem 0 1.5rem 0; text-align: center; text-wrap: stable;">
			@lang( 'We\'ve sent a link to reset your password to' ) <code class="submitted-email"></code>
			<br><br>
			@lang( 'Please check your inbox and follow the instructions in the email.' )
		</p>

		<div style="text-align: center">
			<a class="toggle-login color-text">@lang( 'Back to login' )</a>
		</div>
	</article>

	<article id="verify-email" class="small draggable" style="display: none">
		<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
		<div class="handle"></div>

		<h2>@lang( 'Please check your inbox' )</h2>

		<div class="icon-big aos fade-in-left" data-animation="fade-in-left" data-delay=".5s" style="animation-duration: 0.3s; animation-delay: 0.5s;">
			@include( 'icons.mail-send' )
		</div>

		<p style="margin: 2rem 0 1.5rem 0; text-align: center; text-wrap: stable">
			@lang( 'We\'ve sent a sign up link to' )<br><code class="submitted-email"></code>
		</p>
	</article>
</dialog>

@push( 'scripts' )
<script>
	document.addEventListener( 'DOMContentLoaded', () => {
		// Toggle to signup article.
		$( 'dialog .toggle-signup' ).on( 'click', function ( e ) {
			e.preventDefault();

			closeDialog( $( '#dialog-auth' ) );
			setTimeout( () => window.openDialog( '#dialog-auth', '#signup' ), 500 );
		} );

		// Toggle to login article.
		$( 'dialog .toggle-login' ).on( 'click', function ( e ) {
			e.preventDefault();

			closeDialog( $( '#dialog-auth' ) );
			setTimeout( () => window.openDialog( '#dialog-auth', '#login' ), 500 );
		} );

		// Toggle to forgot password article.
		$( '#toggle-forgot-password' ).on( 'click', function ( e ) {
			e.preventDefault();

			closeDialog( $( '#dialog-auth' ) );
			setTimeout( () => window.openDialog( '#dialog-auth', '#forgot-password' ), 500 );
		} );

		// Toggle back to login from forgot password.
		$( 'dialog #forgot-password .toggle-login' ).on( 'click', function ( e ) {
			e.preventDefault();

			closeDialog( $( '#dialog-auth' ) );
			setTimeout( () => window.openDialog( '#dialog-auth', '#login' ), 500 );
		} );

		// Toggle to reset email sent.
		$( 'dialog #reset-email-sent .toggle-login' ).on( 'click', function ( e ) {
			e.preventDefault();

			closeDialog( $( '#dialog-auth' ) );
			setTimeout( () => window.openDialog( '#dialog-auth', '#login' ), 500 );
		} );

		// Login form submission.
		$( 'dialog #login-form' ).on( 'submit', function ( e ) {
			e.preventDefault();

			// Prevent double submission
			const $form = $( this );
			if ( $form.data( 'submitting' ) ) {
				return;
			}
			$form.data( 'submitting', true );

			const email           = $( 'dialog #login-form input[name="email"]' ).val().trim();
			const password        = $( 'dialog #login-form input[name="password"]' ).val();
			const $emailInput     = $( 'dialog #login-form input[name="email"]' );
			const $passwordInput  = $( 'dialog #login-form input[name="password"]' );
			const $emailHelper    = $( 'dialog #login-form input[name="email"]' ).next( '.invalid-helper' );
			const $passwordHelper = $( 'dialog #login-form input[name="password"]' ).next( '.invalid-helper' );
			const $submitButton   = $( 'dialog #login-form button[type="submit"]' );

			// Add loading state to the submit button.
			$submitButton.attr( 'aria-busy', 'true' ).attr( 'aria-label', '@lang( "Please wait..." )' ).addClass( 'busy' );

			// Clear previous errors.
			$emailInput.removeAttr( 'aria-invalid' );
			$passwordInput.removeAttr( 'aria-invalid' );
			$emailHelper.text( '' );
			$passwordHelper.text( '' );

			$.ajax( {
				url: '{{ route( 'login.post' ) }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					email: email,
					password: password
				},
				success: function ( response ) {
					window.location.reload();
					$submitButton.removeAttr( 'aria-busy' ).removeAttr( 'aria-label' ).removeClass( 'busy' );
				},
				error: function ( xhr ) {
					if ( xhr.status === 422 ) {
						const errors = xhr.responseJSON.errors;

						if ( errors.email ) {
							$emailInput.attr( 'aria-invalid', 'true' );
							$emailHelper.text( errors.email );
						}
						if ( errors.password ) {
							$passwordInput.attr( 'aria-invalid', 'true' );
							$passwordHelper.text( errors.password );
						}
					} else {
						$emailHelper.text( "@lang( 'An unexpected error occurred. Please try again.' )" );
					}

					$submitButton.removeAttr( 'aria-busy' ).removeAttr( 'aria-label' ).removeClass( 'busy' );
					$form.data( 'submitting', false );
				}
			} );
		} );

		// Signup form submission.
		$( 'dialog #signup-form' ).on( 'submit', function ( e ) {
			e.preventDefault();

			// Prevent double submission
			const $form = $( this );
			if ( $form.data( 'submitting' ) ) {
				return;
			}
			$form.data( 'submitting', true );

			const email                  = $( 'dialog #signup-form input[name="email"]' ).val().trim();
			const password               = $( 'dialog #signup-form input[name="password"]' ).val();
			const password_confirmation  = $( 'dialog #signup-form input[name="password_confirmation"]' ).val();
			const $emailInput            = $( 'dialog #signup-form input[name="email"]' );
			const $passwordInput         = $( 'dialog #signup-form input[name="password"]' );
			const $passwordConfirmInput  = $( 'dialog #signup-form input[name="password_confirmation"]' );
			const $emailHelper           = $( 'dialog #signup-form input[name="email"]' ).next( '.invalid-helper' );
			const $passwordHelper        = $( 'dialog #signup-form input[name="password"]' ).next( '.invalid-helper' );
			const $passwordConfirmHelper = $( 'dialog #signup-form input[name="password_confirmation"]' ).next( '.invalid-helper' );
			const $submitButton          = $( 'dialog #signup-form button[type="submit"]' );

			// Add loading state to the submit button.
			$submitButton.attr( 'aria-busy', 'true' ).attr( 'aria-label', '@lang( "Please wait..." )' ).addClass( 'busy' );

			// Clear previous errors.
			$emailInput.removeAttr( 'aria-invalid' );
			$passwordInput.removeAttr( 'aria-invalid' );
			$passwordConfirmInput.removeAttr( 'aria-invalid' );
			$emailHelper.text( '' );
			$passwordHelper.text( '' );
			$passwordConfirmHelper.text( '' );

			$.ajax( {
				url: '{{ route( 'signup.email' ) }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					email: email,
					password: password,
					password_confirmation: password_confirmation
				},
				success: function ( response ) {
					$submitButton.removeAttr( 'aria-busy' ).removeAttr( 'aria-label' ).removeClass( 'busy' );
					// Redirect to dashboard.
					if ( response.redirect ) {
						window.location.href = response.redirect;
					} else {
						window.location.reload();
					}
				},
				error: function ( xhr ) {
					if ( xhr.status === 422 ) {
						const errors = xhr.responseJSON.errors;

						if ( errors.email ) {
							$emailInput.attr( 'aria-invalid', 'true' );
							$emailHelper.text( errors.email );
						}
						if ( errors.password ) {
							$passwordInput.attr( 'aria-invalid', 'true' );
							$passwordHelper.text( errors.password );
						}
						if ( errors.password_confirmation ) {
							$passwordConfirmInput.attr( 'aria-invalid', 'true' );
							$passwordConfirmHelper.text( errors.password_confirmation );
						}
					} else {
						$emailHelper.text( "@lang( 'An unexpected error occurred. Please try again.' )" );
					}

					$submitButton.removeAttr( 'aria-busy' ).removeAttr( 'aria-label' ).removeClass( 'busy' );
					$form.data( 'submitting', false );
				}
			} );
		} );

		$( 'dialog #forgot-password-form' ).on( 'submit', function ( e ) {
			e.preventDefault();

			const $form = $( this );
			if ( $form.data( 'submitting' ) ) {
				return;
			}
			$form.data( 'submitting', true );

			const $emailInput  = $form.find( 'input[name="email"]' );
			const $emailHelper = $emailInput.next( '.invalid-helper' );
			const $submitButton = $form.find( 'button[type="submit"]' );

			$emailInput.removeAttr( 'aria-invalid' );
			$emailHelper.text( '' );
			$submitButton.attr( 'aria-busy', 'true' ).addClass( 'busy' );

			$.ajax( {
				url: '{{ route( 'password.email' ) }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					email: $emailInput.val().trim()
				},
				success: function () {
					// Hide forgot password form and show confirmation screen.
					closeDialog( $( '#dialog-auth' ) );
					setTimeout( () => window.openDialog( '#dialog-auth', '#reset-email-sent' ), 500 );
					$( 'dialog #reset-email-sent .submitted-email' ).text( $emailInput.val().trim() );
				},
				error: function ( xhr ) {
					$emailInput.attr( 'aria-invalid', 'true' );

					if ( xhr.status === 422 ) {
						const errors = xhr.responseJSON.errors;

						if ( errors.email ) {
							$emailHelper.text( errors.email );
						}
					} else {
						$emailHelper.text( 'An error occurred. Please try again.' );
					}
				},
				complete: function () {
					$form.data( 'submitting', false );
					$submitButton.removeAttr( 'aria-busy' ).removeClass( 'busy' );
				}
			} );
		} );
	} );
</script>
@endpush

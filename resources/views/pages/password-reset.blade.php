@extends( 'layouts/main' )
@section( 'title', __( 'Set Your Password' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="password-reset-page" style="max-width: 500px; margin: 80px auto;padding: 1rem;">
			<h1 class="text-center mb-5">@lang( 'Set Your Password' )</h1>

			@if (session( 'error' ) )
				<x-alert type="error" :message="session('error')" />
			@endif

			@if ( session( 'success' ) )
				<x-alert type="success" :message="session('success')" />
			@endif

			<p class="text-center mb-7">
				@if ( $token)
					@lang('Create a new password for your account.')
				@else
					@lang('Enter your email address and we will send you a link to reset your password.')
				@endif
			</p>

			@if ( $token )
				<!-- Password Reset Form -->
				<form method="POST" action="{{ route('password.update') }}">
					@csrf
					<input type="hidden" name="token" value="{{ $token }}">
					<input type="hidden" name="email" value="{{ $email }}">

					<label for="password">@lang( 'New Password' )</label>
					<input
						type="password"
						id="password"
						name="password"
						placeholder="@lang('Enter your new password')"
						required
						autofocus
						aria-invalid="{{ $errors->has('password') ? 'true' : '' }}"
					>
					@if($errors->has('password'))
						<small>
							{{ $errors->first('password') }}
						</small>
					@endif

					<label for="password_confirmation">@lang( 'Confirm Password' )</label>
					<input
						type="password"
						id="password_confirmation"
						name="password_confirmation"
						placeholder="@lang('Confirm your new password')"
						required
						aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : '' }}"
					>
					@if($errors->has('password_confirmation'))
						<small>
							{{ $errors->first('password_confirmation') }}
						</small>
					@endif

					<button type="submit" class="contrast mt-4 mb-0">
						@lang( 'Set Password' )
					</button>
				</form>
			@else
				<!-- Password Reset Request Form -->
				<form method="POST" action="{{ route('password.email') }}">
					@csrf

					<label for="email">@lang( 'Email address' )</label>
					<input
						type="email"
						id="email"
						name="email"
						placeholder="@lang('john@example.com')"
						required
						autofocus
						aria-invalid="{{ $errors->has('email') ? 'true' : '' }}"
						value="{{ old('email') }}"
					>
					@if($errors->has('email'))
						<small>
							{{ $errors->first('email') }}
						</small>
					@endif

					<button type="submit" class="contrast mt-4 mb-0">
						@lang( 'Send Reset Link' )
					</button>
				</form>
			@endif

			<div class="text-center mt-5">
				<p>
					<a href="{{ route('login') }}">@lang( 'Back to login' )</a>
				</p>
			</div>
		</div>
	</main>
@endsection

@extends( 'layouts/main' )
@section( 'title', __( 'Log in' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="login-page" style="max-width: 500px; margin: 80px auto;padding: 1rem;"
			x-data="{
				step: 1,
				email: '{{ old('email') }}',
				isLoading: false,
				message: '',
				messageType: '',
				hasPassword: false,

				async checkEmail() {
					if (!this.email) return;

					this.isLoading = true;
					this.message = '';

					try {
						const response = await fetch('{{ route('login.check-email') }}', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-CSRF-TOKEN': '{{ csrf_token() }}',
								'Accept': 'application/json'
							},
							body: JSON.stringify({ email: this.email })
						});

						const data = await response.json();

						if (data.status === 'has_password') {
							this.hasPassword = true;
							this.step = 2;
							this.message = '';
							// Focus password field after a short delay
							setTimeout(() => {
								document.getElementById('password')?.focus();
							}, 100);
						} else if (data.status === 'no_password') {
							this.message = data.message;
							this.messageType = 'info';
						} else if (data.status === 'error') {
							this.message = data.message;
							this.messageType = 'error';
						}
					} catch (error) {
						this.message = '{{ __('An unexpected error occurred.') }}';
						this.messageType = 'error';
					} finally {
						this.isLoading = false;
					}
				},

				handleSubmit(event) {
					if (this.step === 1) {
						event.preventDefault();
						this.checkEmail();
					}
					// If step 2, let the form submit normally
				}
			}">
			<h1 class="text-center mb-5">@lang( 'Log in' )</h1>

			@if ( session( 'error' ) )
				<x-alert type="error" :message="session('error')" />
			@endif

			@if(session('success'))
				<x-alert type="success" :message="session('success')" />
			@endif

			<!-- Dynamic message from Alpine.js -->
			<div x-show="message" x-transition>
				<div class="alert-banner" :style="`background-color: ${messageType === 'error' ? '#FEE2E2' : '#d1e7dd'}; color: ${messageType === 'error' ? '#842029' : '#0f5132'};`">
					<div class="alert-banner-content">
						<div class="alert-banner-icon">
							<template x-if="messageType === 'error'">
								@include('icons.warning')
							</template>
							<template x-if="messageType === 'info'">
								<svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
									<polyline points="22 4 12 14.01 9 11.01"></polyline>
								</svg>
							</template>
						</div>
						<div>
							<strong x-text="message"></strong>
						</div>
					</div>
				</div>
			</div>

			<p class="text-center mb-7">
				@lang('Welcome back! Log in to view and manage your visa applications.')
			</p>

			<form method="POST" action="{{ route('login.post') }}" @submit="handleSubmit">
				@csrf

				<label for="email">@lang( 'Email address' )</label>
				<input
					type="email"
					id="email"
					name="email"
					x-model="email"
					placeholder="@lang('john@example.com')"
					required
					autofocus
					:readonly="step === 2"
				>

				<div x-show="step === 2 && hasPassword" x-transition>
					<label for="password">@lang( 'Password' )</label>
					<input
						type="password"
						id="password"
						name="password"
						placeholder="@lang('Your password')"
						:required="step === 2"
					>
				</div>

				<button type="submit" class="contrast mt-6 mb-0" :aria-busy="isLoading">
					<span x-show="!isLoading">
						<span x-show="step === 1">@lang( 'Continue' )</span>
						<span x-show="step === 2">@lang( 'Log in' )</span>
					</span>
				</button>
			</form>

			<div class="text-center mt-5">
				<p>
					<a href="{{ route('password.reset') }}">@lang('Forgot your password?')</a>
				</p>
				<p class="mt-3">
					@lang("Don't have an account?")
					<a href="{{ route('home') }}">@lang('Start a new application')</a>
				</p>
			</div>
		</div>
	</main>
@endsection

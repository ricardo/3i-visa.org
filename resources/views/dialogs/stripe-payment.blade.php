<dialog id="dialog-stripe-payment" class="dialog floating" aria-expanded="false" aria-label="Payment">
	<article
		id="stripe-payment"
		class="small draggable"
		data-error-invalid-response="@lang( 'Server returned invalid response' )"
		data-error-init-payment="@lang( 'Failed to initialize payment' )"
		data-error-init-payment-retry="@lang( 'Failed to initialize payment. Please try again.' )"
		data-error-not-initialized="@lang( 'Payment not initialized' )"
		data-error-unexpected="@lang( 'An unexpected error occurred.' )"
		x-data="{
			stripe: null,
			elements: null,
			paymentElement: null,
			isLoading: false,
			error: null,
			clientSecret: null,
			publicKey: null,
			initialized: false,

			resetStripe() {
				// Reset all Stripe-related state to allow re-initialization
				// This ensures a fresh payment intent is created if pricing changes
				this.stripe = null;
				this.elements = null;
				this.paymentElement = null;
				this.isLoading = false;
				this.error = null;
				this.clientSecret = null;
				this.publicKey = null;
				this.initialized = false;
			},

			async initStripe() {
				// Only initialize once per modal open
				if (this.initialized) return;
				this.initialized = true;
				// Get payment intent from server
				try {
					const response = await fetch('{{ route('payment.intent', ['country' => $country_slug ?? 'colombia']) }}', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-CSRF-TOKEN': '{{ csrf_token() }}'
						}
					});

					// Check if response is OK
					if (!response.ok) {
						const text = await response.text();
						console.error('Server error:', response.status, text);
						this.error = `Server error: ${response.status}`;
						return;
					}

					// Check if response is JSON
					const contentType = response.headers.get('content-type');
					if (!contentType || !contentType.includes('application/json')) {
						const text = await response.text();
						console.error('Non-JSON response:', text.substring(0, 200));
						this.error = this.$el.dataset.errorInvalidResponse;
						return;
					}

					const data = await response.json();

					if (!data.success) {
						console.error('Payment intent error:', data);
						this.error = data.message || this.$el.dataset.errorInitPayment;
						return;
					}

					this.clientSecret = data.client_secret;
					this.publicKey = data.public_key;

					// Load Stripe.js if not already loaded
					if (!window.Stripe) {
						await this.loadStripeScript();
					}

					// Initialize Stripe
					this.stripe = Stripe(this.publicKey);

					// Create elements
					this.elements = this.stripe.elements({
						clientSecret: this.clientSecret,
						appearance: {
							theme: 'stripe',
							variables: {
								colorPrimary: '#0066cc',
								borderRadius: '8px',
								fontWeightNormal: '600',
								spacingUnit: '6px',
							}
						}
					});

					// Create and mount payment element
					this.paymentElement = this.elements.create('payment');
					this.paymentElement.mount('#payment-element');

				} catch (err) {
					console.error('Error initializing Stripe:', err);
					this.error = this.$el.dataset.errorInitPaymentRetry;
				}
			},

			loadStripeScript() {
				return new Promise((resolve, reject) => {
					const script = document.createElement('script');
					script.src = 'https://js.stripe.com/v3/';
					script.onload = resolve;
					script.onerror = reject;
					document.head.appendChild(script);
				});
			},

			async handleSubmit() {
				if (!this.stripe || !this.elements) {
					this.error = this.$el.dataset.errorNotInitialized;
					return;
				}

				this.isLoading = true;
				this.error = null;

				const { error } = await this.stripe.confirmPayment({
					elements: this.elements,
					confirmParams: {
						return_url: '{{ route('order') }}',
					},
				});

				// This point will only be reached if there is an immediate error when
				// confirming the payment. Otherwise, the customer will be redirected to
				// the `return_url`. For some payment methods like iDEAL, the customer will
				// be redirected to an intermediate site first to authorize the payment, then
				// redirected to the `return_url`.
				if (error) {
					if (error.type === 'card_error' || error.type === 'validation_error') {
						this.error = error.message;
					} else {
						this.error = this.$el.dataset.errorUnexpected;
					}
				}

				this.isLoading = false;
			}
		}"
	>
		<a
			href="javascript: void(0);"
			aria-label="@lang( 'Close' )"
			class="close icon-link"
			x-on:click="resetStripe()"
		></a>
		<div class="handle"></div>

		<h2>@lang( 'Continue to payment' )</h2>

		<div class="mt-7">
			<!-- Loading State -->
			<div x-show="!clientSecret && !error" class="text-center mb-5">
				<p aria-busy="true">@lang( 'Loading payment form...' )</p>
			</div>

			<!-- Payment Form -->
			<form id="payment-form" x-on:submit.prevent="handleSubmit" x-show="clientSecret || error">
				<!-- Stripe Payment Element will be mounted here -->
				<div id="payment-element" class="mb-5" style="--stripe-border-radius: 8px;"></div>

				<!-- Error Message -->
				<div x-show="error" x-text="error" class="error-message mb-5" style="color: red;"></div>

				<!-- Submit Button -->
				<button
					type="submit"
					class="contrast mt-4 mb-0 flex"
					x-bind:disabled="isLoading || !clientSecret"
					x-bind:aria-busy="isLoading"
				>
					<span x-show="!isLoading" style="margin-right: 12px;">
						@include('icons.lock')
					</span>
					<span x-show="!isLoading">
						@php
							// Format the total amount
							$formatted_amount = number_format(
								$total_price,
								$currency_config['decimal_places'],
								$currency_config['decimal_separator'],
								$currency_config['thousands_separator']
							);

							// Build the display string based on symbol position
							if ($currency_config['symbol_position'] === 'after') {
								$display_total = $formatted_amount . ' ' . $currency_symbol;
							} else {
								$display_total = $currency_symbol . $formatted_amount;
							}
						@endphp
						Pay {{ $display_total }} {{ strtoupper($currency_code) }}
					</span>
					<span x-show="isLoading">@lang( 'Processing...' )</span>
				</button>
			</form>

			<style>
				/* Increase border-radius for Stripe input elements */
				#payment-element .StripeElement,
				#payment-element iframe {
					border-radius: 8px !important;
				}
			</style>
		</div>
	</article>
</dialog>

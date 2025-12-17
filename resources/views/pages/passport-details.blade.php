@extends( 'layouts/main' )
@section( 'title', __( 'Passport Details' ) . ' • 3i Visa' )
@section( 'content' )
	<!-- Mobile Progress Bar -->
	<div class="display-mobile-only">
		<x-mobile-progress-bar :current_step="3" :total_steps="4" />
	</div>

	<main class="container">
		<!-- Main Page Title -->
		<h1 class="application-details-main-title">@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h1>

		<!-- Progress Steps (Desktop Only) -->
		<div class="display-desktop-only">
			<x-progress-steps :current_step="2.5" />
		</div>

		<script>
			document.addEventListener('alpine:init', () => {
				Alpine.data('passportDetailsPage', () => ({
					pricePerTraveler: {{ $price_per_traveler }},
					currencySymbol: {{ Js::from($currency_symbol) }},
					currencyConfig: {
						decimal_places: {{ $currency_config['decimal_places'] }},
						thousands_separator: {{ Js::from($currency_config['thousands_separator']) }},
						decimal_separator: {{ Js::from($currency_config['decimal_separator']) }},
						symbol_position: {{ Js::from($currency_config['symbol_position']) }}
					},
					travelerCount: {{ $applicants_count }},

					formatCurrency(amount) {
						const parts = amount.toFixed(this.currencyConfig.decimal_places).split('.');
						parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.currencyConfig.thousands_separator);
						const formatted = parts.join(this.currencyConfig.decimal_separator);

						if (this.currencyConfig.symbol_position === 'after') {
							return formatted + ' ' + this.currencySymbol;
						} else {
							return this.currencySymbol + formatted;
						}
					},

					get totalPrice() {
						return (this.travelerCount * this.pricePerTraveler);
					},

					get formattedTotal() {
						return this.formatCurrency(this.totalPrice);
					},
					hasErrors: false,
					errors: {},
					isSubmitting: false,
					async submitForm(event) {
						event.preventDefault();

						if (this.isSubmitting) return;

						this.isSubmitting = true;
						this.hasErrors = false;
						this.errors = {};

						const formData = new FormData(event.target);

						try {
							const response = await axios.post(
								event.target.action,
								formData,
								{
									headers: {
										'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
										'Accept': 'application/json'
									}
								}
							);

							// Success - redirect to next step
							if (response.data.success) {
								window.location.href = response.data.redirect;
							}
						} catch (error) {
							if (error.response && error.response.status === 422) {
								// Validation errors
								this.errors = error.response.data.errors;
								this.hasErrors = true;

								// Scroll to error banner
								this.$nextTick(() => {
									if (this.$refs.errorBanner) {
										this.$refs.errorBanner.scrollIntoView({ behavior: 'smooth', block: 'start' });
									}
								});
							}
						} finally {
							this.isSubmitting = false;
						}
					},
					getError(field) {
						return this.errors[field] ? this.errors[field][0] : '';
					},
					hasError(field) {
						return !!this.errors[field];
					},
					clearFieldError(field) {
						if (this.errors[field]) {
							delete this.errors[field];
							// Check if there are any errors left
							if (Object.keys(this.errors).length === 0) {
								this.hasErrors = false;
							}
						}
					}
				}));
			});
		</script>

		<div class="application-details-page" x-data="passportDetailsPage">
			<div class="application-details-content">
				<form
					id="passport-details-form"
					action="{{ route( 'passport.details.submit', [ 'country' => $country_slug ] ) }}"
					method="POST"
					x-on:submit.prevent="submitForm($event)"
				>
					@csrf

					<!-- Validation Error Banner -->
					<div
						x-show="hasErrors"
						x-transition
						class="validation-error-banner"
						role="alert"
						style="display: none;"
						x-ref="errorBanner"
					>
						<div class="error-banner-content flex">
							<div class="error-banner-icon">
								@include( 'icons.warning' )
							</div>
							<div>
								<strong>@lang( 'Please fix the fields highlighted in red' )</strong>
							</div>
						</div>
					</div>

					<!-- Page Title -->
					<div class="application-details-title">
						<h1 class="application-details-heading">@lang( 'Passport details' )</h1>
					</div>

					<!-- Traveler Accordions -->
					<div class="travelers-list">
						@foreach( $travelers as $index => $traveler )
							<x-passport-accordion
								:traveler_index="$index"
								:traveler_name="$traveler['first_name'] ?? ''"
								:initial_expanded="$loop->first"
								:initial_nationality="$traveler['nationality'] ?? $nationality ?? null"
								:initial_passport_number="$traveler['passport_number'] ?? ''"
								:initial_passport_expiration_month="$traveler['passport_expiration_month'] ?? ''"
								:initial_passport_expiration_day="$traveler['passport_expiration_day'] ?? ''"
								:initial_passport_expiration_year="$traveler['passport_expiration_year'] ?? ''"
								:initial_add_passport_later="isset($traveler['add_passport_later']) && $traveler['add_passport_later'] ? true : false"
							/>
						@endforeach
					</div>
				</form>
			</div>

			<!-- Order Summary Sidebar -->
			<aside class="application-details-sidebar display-desktop-only">
				<!-- Order Summary Card -->
				<div class="order-summary-card">
					<div class="order-summary-row">
						<div class="order-summary-title">@lang(':country Check-MIG Form', ['country' => $country_name])</div>
						<div class="order-summary-value">
							<span x-text="travelerCount"></span> <span x-text="travelerCount === 1 ? '@lang('traveler')' : '@lang('travelers')'"></span>
						</div>
					</div>
				</div>

				<!-- Total Section -->
				<div class="order-total-section">
					<div class="order-total-label">@lang( 'Total' )</div>
					<div class="order-total-calculated">@lang( 'Calculated at checkout' )</div>
				</div>

				<!-- Save and Continue Button -->
				<button
					type="submit"
					form="passport-details-form"
					class="apply-submit-button mb-0"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Save and continue' )
				</button>

				<!-- Security Message -->
				<div class="security-message">
					<div class="security-message-icon">
						@include('icons.shield-check')
					</div>
					<div class="security-message-text">
						<strong>@lang('We take strong measures to protect your information.')</strong>
						<p>@lang('For more details see') <a href="#">@lang('how we keep your data safe')</a></p>
					</div>
				</div>

				<!-- Previous Link -->
				<a href="{{ route('application.details', ['country' => $country_slug]) }}" class="previous-link">
					@include('icons.arrow-left', ['class' => 'mr-2'])
					@lang('Previous')
				</a>
			</aside>

			<!-- Mobile/Tablet Fixed Bottom Button -->
			<div class="apply-mobile-submit display-mobile-only">
				<div class="mobile-total-section">
					<div class="mobile-total-label">@lang('Total')</div>
					<div class="mobile-total-calculated">@lang('Calculated at checkout')</div>
				</div>
				<button
					type="submit"
					form="passport-details-form"
					class="apply-mobile-submit-button"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Save and continue' )
				</button>
			</div>
		</div>
	</main>
@endsection

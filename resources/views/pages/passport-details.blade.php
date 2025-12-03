@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Passport Details' ) . ' • 3i Visa' )
@section( 'content' )
	<!-- Mobile Progress Bar -->
	<div class="display-mobile-only">
		<x-mobile-progress-bar :current_step="3.5" :total_steps="4" />
	</div>

	<main class="container">
		<!-- Main Page Title -->
		<h1 class="application-details-main-title">@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h1>

		<!-- Progress Steps (Desktop Only) -->
		<div class="display-desktop-only">
			<x-progress-steps :current_step="2.5" />
		</div>

		<div class="application-details-page" x-data="{
					pricePerTraveler: {{ $price_per_traveler }},
					currencySymbol: '{{ $currency_symbol }}',
					travelerCount: {{ $applicants_count }},
					get totalPrice() {
						return (this.travelerCount * this.pricePerTraveler).toFixed(2);
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
				}"
			>
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
					<div class="order-summary-row">
						<div class="order-summary-label">@lang('Standard processing')</div>
						<div class="order-summary-value">
							<span x-text="currencySymbol"></span><span x-text="totalPrice"></span>
						</div>
					</div>
				</div>

				<!-- Total Section -->
				<div class="order-total-section">
					<div class="order-total-label">@lang('Total')</div>
					<div class="order-total-calculated">@lang('Calculated at checkout')</div>
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
		</div>

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
	</main>
@endsection

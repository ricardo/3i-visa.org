@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Review Your Order' ) . ' • 3i Visa' )
@section( 'content' )
	<!-- Mobile Progress Bar -->
	<div class="display-mobile-only">
		<x-mobile-progress-bar :current_step="4" :total_steps="4" />
	</div>

	<main class="container">
		<!-- Main Page Title -->
		<h1 class="application-details-main-title">@lang( 'Review your order' )</h1>

		<!-- Progress Steps (Desktop Only) -->
		<!-- <div class="display-desktop-only">
			<x-progress-steps :current_step="3" />
		</div> -->

		<script>
			document.addEventListener('alpine:init', () => {
				Alpine.data('reviewPage', () => ({
					pricePerTraveler: {{ $price_per_traveler }},
					currencySymbol: {{ Js::from($currency_symbol) }},
					currencyConfig: {
						decimal_places: {{ $currency_config['decimal_places'] }},
						thousands_separator: {{ Js::from($currency_config['thousands_separator']) }},
						decimal_separator: {{ Js::from($currency_config['decimal_separator']) }},
						symbol_position: {{ Js::from($currency_config['symbol_position']) }}
					},
					travelerCount: {{ $applicants_count }},
					processingFee: {{ $processing_fee }},
					denialProtectionPrice: {{ $denial_protection_price }},
					hasDenialProtection: {{ $has_denial_protection ? 'true' : 'false' }},
					isSubmitting: false,
					isUpdatingDenialProtection: false,

					init() {
						// Watch for changes to hasDenialProtection and save to session
						this.$watch('hasDenialProtection', (value) => {
							this.updateDenialProtection(value);
						});
					},

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

					async updateDenialProtection(value) {
						if (this.isUpdatingDenialProtection) return;

						this.isUpdatingDenialProtection = true;

						const formData = new FormData();
						formData.append('denial_protection', value ? '1' : '0');

						try {
							const response = await axios.post(
								'{{ route('review.update.denial', ['country' => $country_slug]) }}',
								formData,
								{
									headers: {
										'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
										'Accept': 'application/json'
									}
								}
							);

							if (response.data.success) {
								console.log('Denial protection updated:', response.data.has_denial_protection);
							}
						} catch (error) {
							console.error('Failed to update denial protection:', error);
						} finally {
							this.isUpdatingDenialProtection = false;
						}
					},

					get baseTotal() {
						return (this.travelerCount * this.pricePerTraveler);
					},

					get denialProtectionTotal() {
						return this.hasDenialProtection ? parseFloat(this.denialProtectionPrice) : 0;
					},

					get totalPrice() {
						return (parseFloat(this.baseTotal) + parseFloat(this.processingFee) + this.denialProtectionTotal);
					},

					get formattedTotal() {
						return this.formatCurrency(this.totalPrice);
					},

					get formattedDenialProtectionPrice() {
						return this.formatCurrency(this.denialProtectionPrice);
					},

					get formattedDenialProtectionPriceOnly() {
						const parts = this.denialProtectionPrice.toFixed(this.currencyConfig.decimal_places).split('.');
						parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.currencyConfig.thousands_separator);
						return parts.join(this.currencyConfig.decimal_separator);
					},

					async submitForm(event) {
						event.preventDefault();

						if (this.isSubmitting) return;

						this.isSubmitting = true;

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

							if (response.data.success) {
								window.location.href = response.data.redirect;
							}
						} catch (error) {
							console.error('Failed to submit form:', error);
						} finally {
							this.isSubmitting = false;
						}
					}
				}));
			});
		</script>

		<div class="application-details-page" x-data="reviewPage">
			<div class="application-details-content">
				<form
					id="review-form"
					action="{{ route( 'review.submit', [ 'country' => $country_slug ] ) }}"
					method="POST"
					x-on:submit.prevent="submitForm($event)"
				>
					@csrf

					<!-- Arrival Date Badge -->
					<div class="review-date-badge">
						<div class="review-date-icon">
							@include( 'icons.calendar' )
						</div>
						<div class="review-date-text">
							{{ ucfirst( $arrival_date->translatedFormat( 'l, M j, Y' ) ) }}
						</div>
					</div>

					<!-- Visa Details Card -->
					<div class="review-visa-card">
						<!-- Processing Badge -->
						<div class="review-processing-badge">
							{{ __( $processing_name . ' Processing' ) }}
						</div>

						<!-- Card Header -->
						<div class="review-visa-header">
							<h2 class="review-visa-title">{{ $country_name }} Check-MIG Form</h2>
							<div class="review-country-flag">
								<span class="fi fi-{{ strtolower( $country_code ) }}"></span>
							</div>
						</div>

						<!-- Visa Information -->
						<div class="review-visa-info">
							<div class="review-visa-info-item">
								<span class="review-visa-label">@lang('Valid for:') </span>
								<span class="review-visa-value">{{ __( $visa_details['valid_for_description'] ) }}</span>
							</div>
							<div class="review-visa-info-item">
								<span class="review-visa-label">@lang('Max stay:') </span>
								<span class="review-visa-value">{{ __( $visa_details['max_stay_description'] ) }}</span>
							</div>
							<div class="review-visa-info-item">
								<span class="review-visa-label">@lang('Number of entries:') </span>
								<span class="review-visa-value">{{ __( $visa_details['entries_description'] ) }}</span>
							</div>
						</div>

						<!-- Travelers List -->
						<div class="review-travelers">
							<h3 class="review-travelers-title">@lang('Travelers:')</h3>
							@foreach( $travelers as $index => $traveler )
								<div class="review-traveler-item">
									<div class="review-traveler-icon">
										@include('icons.user')
									</div>
									<div class="review-traveler-name">
										{{ $traveler['first_name'] ?? '' }} {{ $traveler['last_name'] ?? '' }}
									</div>
								</div>
							@endforeach
						</div>
					</div>

					<!-- Denial Protection -->
					<div
						class="review-denial-protection"
						x-bind:class="{ 'selected': hasDenialProtection }"
						x-on:click="hasDenialProtection = !hasDenialProtection"
					>
						<div class="review-denial-checkbox">
							<input
								type="checkbox"
								name="denial_protection"
								id="denial_protection"
								value="1"
								x-model="hasDenialProtection"
								@if($has_denial_protection) checked @endif
							>
						</div>
						<div class="review-denial-content">
							<div class="review-denial-header">
								@include( 'icons.shield-check' )
								<label class="review-denial-title">@lang('Add denial protection')</label>
								<div class="review-denial-price">+ <span x-text="currencySymbol"></span><span x-text="formattedDenialProtectionPriceOnly"></span></div>
							</div>
							<div class="review-denial-description">
								{{ $denial_protection['description'] }}
							</div>
						</div>
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
							<span x-text="travelerCount"></span> <span x-text="travelerCount === 1 ? '@lang('Traveler')' : '@lang('Travelers')'"></span>
						</div>
					</div>
					<div class="order-summary-row">
						<div class="order-summary-label">{{ __( $processing_name ) }}, {{ $processing_days }} {{ $processing_days === 1 ? __( 'Day' ) : __( 'Days' ) }}</div>
						<div class="order-summary-value">
							<span x-text="formattedTotal"></span>
						</div>
					</div>
				</div>

				<!-- Total Section -->
				<div class="order-total-section">
					<div class="order-total-label">@lang('Total')</div>
					<div class="order-total-price">
						<span x-text="formattedTotal"></span>
					</div>
				</div>

				<!-- Continue to Payment Button -->
				<button
					type="submit"
					form="review-form"
					class="apply-submit-button mb-0"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Continue to payment' )
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
				<a href="{{ route('processing.time', ['country' => $country_slug]) }}" class="previous-link">
					@include('icons.arrow-left', ['class' => 'mr-2'])
					@lang('Previous')
				</a>
			</aside>

			<!-- Mobile/Tablet Fixed Bottom Button -->
			<div class="apply-mobile-submit display-mobile-only">
				<div class="mobile-total-section">
					<div class="mobile-total-label">@lang('Total')</div>
					<div class="mobile-total-price">
						<span x-text="formattedTotal"></span>
					</div>
				</div>
				<button
					type="submit"
					form="review-form"
					class="apply-mobile-submit-button"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Continue to payment' )
				</button>
			</div>
		</div>
	</main>
@endsection

@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Processing Time' ) . ' • 3i Visa' )
@section( 'content' )
	<!-- Mobile Progress Bar -->
	<div class="display-mobile-only">
		<x-mobile-progress-bar :current_step="3.2" :total_steps="4" />
	</div>

	<main class="container">
		<!-- Main Page Title -->
		<h1 class="application-details-main-title">@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h1>

		<!-- Progress Steps (Desktop Only) -->
		<div class="display-desktop-only">
			<x-progress-steps :current_step="3" />
		</div>

		<div class="application-details-page" x-data="{
				pricePerTraveler: {{ $price_per_traveler }},
				currencySymbol: '{{ $currency_symbol }}',
				travelerCount: {{ $applicants_count }},
				processingFee: {{ $processing_options[$selected_processing]['price_converted'] }},
				selectedProcessing: '{{ $selected_processing }}',
				isSubmitting: false,
				isUpdating: false,
				get baseTotal() {
					return (this.travelerCount * this.pricePerTraveler).toFixed(2);
				},
				get totalPrice() {
					return (parseFloat(this.baseTotal) + parseFloat(this.processingFee)).toFixed(2);
				},
				async updateProcessing(option) {
					if (this.isUpdating || this.selectedProcessing === option) return;

					this.isUpdating = true;
					this.selectedProcessing = option;

					const formData = new FormData();
					formData.append('processing_option', option);

					try {
						const response = await axios.post(
							'{{ route('processing.time.update', ['country' => $country_slug]) }}',
							formData,
							{
								headers: {
									'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
									'Accept': 'application/json'
								}
							}
						);

						if (response.data.success) {
							this.processingFee = response.data.processing_fee;
						}
					} catch (error) {
						console.error('Failed to update processing option:', error);
					} finally {
						this.isUpdating = false;
					}
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

						// Success - redirect to next step
						if (response.data.success) {
							window.location.href = response.data.redirect;
						}
					} catch (error) {
						console.error('Failed to submit form:', error);
					} finally {
						this.isSubmitting = false;
					}
				}
			}"
		>
			<div class="application-details-content">
				<form
					id="processing-time-form"
					action="{{ route( 'processing.time.submit', [ 'country' => $country_slug ] ) }}"
					method="POST"
					x-on:submit.prevent="submitForm($event)"
				>
					@csrf

					<!-- Page Title -->
					<div class="application-details-title">
						<h1 class="application-details-heading">@lang( 'Select processing time' )</h1>
					</div>

					<!-- Processing Options -->
					<div class="processing-options">
						@foreach( $processing_options as $key => $option )
							<div
								class="processing-option-card"
								x-bind:class="{ 'selected': selectedProcessing === '{{ $key }}' }"
								x-on:click="updateProcessing('{{ $key }}')"
							>
								<div class="processing-option-radio">
									<input
										type="radio"
										name="processing_option"
										value="{{ $key }}"
										id="processing-{{ $key }}"
										x-bind:checked="selectedProcessing === '{{ $key }}'"
										required
									>
								</div>
								<div class="processing-option-content">
									<div class="processing-option-header">
										<label for="processing-{{ $key }}" class="processing-option-name">
											{{ $option['name'] }}
										</label>
										<div class="processing-option-price">
											@if( $option['price_usd'] > 0 )
												+ {{ $currency_symbol }}{{ number_format( $option['price_converted'], 2 ) }}
											@else
												+ {{ $currency_symbol }}0
											@endif
										</div>
									</div>
									<div class="processing-option-description">
										{{ $option['description'] }}
									</div>
								</div>
							</div>
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
						<div class="order-summary-label">
							<span x-show="selectedProcessing === 'standard'">@lang('Standard, 3 days')</span>
							<span x-show="selectedProcessing === 'rush'">@lang('Rush, 1 day')</span>
						</div>
						<div class="order-summary-value">
							<span x-text="currencySymbol"></span><span x-text="totalPrice"></span>
						</div>
					</div>
				</div>

				<!-- Total Section -->
				<div class="order-total-section">
					<div class="order-total-label">@lang('Total')</div>
					<div class="order-total-price">
						<span x-text="currencySymbol"></span><span x-text="totalPrice"></span>
					</div>
				</div>

				<!-- Save and Continue Button -->
				<button
					type="submit"
					form="processing-time-form"
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
				<a href="{{ route('passport.details', ['country' => $country_slug]) }}" class="previous-link">
					@include('icons.arrow-left', ['class' => 'mr-2'])
					@lang('Previous')
				</a>
			</aside>

			<!-- Mobile/Tablet Fixed Bottom Button -->
			<div class="apply-mobile-submit display-mobile-only">
				<div class="mobile-total-section">
					<div class="mobile-total-label">@lang('Total')</div>
					<div class="mobile-total-price">
						<span x-text="currencySymbol"></span><span x-text="totalPrice"></span>
					</div>
				</div>
				<button
					type="submit"
					form="processing-time-form"
					class="apply-mobile-submit-button"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Save and continue' )
				</button>
			</div>
		</div>
	</main>
@endsection

@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Application Details' ) . ' • 3i Visa' )
@section( 'content' )
	<!-- Mobile Progress Bar -->
	<div class="display-mobile-only">
		<x-mobile-progress-bar :current_step="2.5" :total_steps="4" />
	</div>

	<main class="container">
		<!-- Main Page Title -->
		<h1 class="application-details-main-title">@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h1>

		<!-- Progress Steps (Desktop Only) -->
		<div class="display-desktop-only">
			<x-progress-steps :current_step="2" />
		</div>

		<div class="application-details-page" x-data="{
					pricePerTraveler: {{ $price_per_traveler }},
					currencySymbol: '{{ $currency_symbol }}',
					activeTravelers: Array.from({ length: {{ $applicants_count }} }, (_, i) => i + 1),
					maxTravelers: 10,
					nextTravelerIndex: {{ $applicants_count + 1 }},
					get travelerCount() {
						return this.activeTravelers.length;
					},
					get totalPrice() {
						return (this.activeTravelers.length * this.pricePerTraveler).toFixed(2);
					},
						hasErrors: false,
						errors: {},
						isSubmitting: false,
						init() {
							this.$nextTick(() => {
								this.broadcastSequentialNumbers();
							});
						},
						broadcastSequentialNumbers() {
							this.activeTravelers.forEach((travelerIndex, arrayPosition) => {
								const sequentialNumber = arrayPosition + 1;
								this.$dispatch('update-sequential-number', {
									travelerIndex: travelerIndex,
									sequentialNumber: sequentialNumber
								});
							});
						},
						addTraveler() {
							if (this.activeTravelers.length < this.maxTravelers) {
								this.activeTravelers.push(this.nextTravelerIndex);
								this.$nextTick(() => {
									this.$dispatch('expand-traveler', this.nextTravelerIndex);
									this.broadcastSequentialNumbers();
								});
								this.nextTravelerIndex++;
							}
						},
						removeTraveler(index) {
							if (this.activeTravelers.length > 1) {
								this.activeTravelers = this.activeTravelers.filter(t => t !== index);
								this.$nextTick(() => {
									this.broadcastSequentialNumbers();
								});
							}
						},
						isTravelerActive(index) {
							return this.activeTravelers.includes(index);
						},
						async submitForm(event) {
							event.preventDefault();

							if (this.isSubmitting) return;

							this.isSubmitting = true;
							this.hasErrors = false;
							this.errors = {};

							// Get all form data
							const formData = new FormData(event.target);

							// Create new FormData with only active travelers
							const filteredFormData = new FormData();

							// Add CSRF token
							filteredFormData.append('_token', formData.get('_token'));

							// Only include data from active travelers
							for (const [key, value] of formData.entries()) {
								if (key === '_token') continue;

								// Check if this is a traveler field
								const match = key.match(/^travelers\[(\d+)\]/);
								if (match) {
									const travelerIndex = parseInt(match[1]);
									// Only include if traveler is active
									if (this.activeTravelers.includes(travelerIndex)) {
										filteredFormData.append(key, value);
									}
								} else {
									// Include non-traveler fields
									filteredFormData.append(key, value);
								}
							}

							try {
								const response = await axios.post(
									event.target.action,
									filteredFormData,
									{
										headers: {
											'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
											'Accept': 'application/json'
										}
									}
								);

								// Success - could redirect or show success message
								if (response.data.success) {
									// TODO: Redirect to next step or show success
									console.log('Success:', response.data.message);
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
					x-on:remove-traveler.window="removeTraveler($event.detail)"
				>
			<div class="application-details-content">
				<form
					id="application-details-form"
					action="{{ route( 'application.details.submit', [ 'country' => $country_slug ] ) }}"
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

					<!-- Page Title and Subtitle -->
					<div class="application-details-title">
						<h1 class="application-details-heading">@lang( 'Your personal details' )</h1>
						<p class="application-details-subtitle">@lang( 'Enter the details as they appear on your passport.' )</p>
					</div>

					<!-- Traveler Accordions -->
					<div class="travelers-list">
						@foreach( range( 1, 10 ) as $index )
							<div x-show="isTravelerActive({{ $index }})">
								<x-traveler-accordion :traveler_index="$index" :is_first="$index === 1" :initial_expanded="$index === 1" />
							</div>
						@endforeach
					</div>

					<!-- Add Another Traveler Button -->
					<button
						type="button"
						class="add-traveler-button flex"
						x-on:click="addTraveler()"
						x-show="activeTravelers.length < maxTravelers"
					>
						@include( 'icons.user-plus', [ 'class' => 'mr-2' ] )
						@lang('Add another traveler')
					</button>
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
					form="application-details-form"
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
				<a href="{{ route('apply', ['country' => $country_slug]) }}" class="previous-link">
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
				form="application-details-form"
				class="apply-mobile-submit-button"
				x-bind:aria-busy="isSubmitting"
			>
				@lang( 'Save and continue' )
			</button>
		</div>
	</main>
@endsection

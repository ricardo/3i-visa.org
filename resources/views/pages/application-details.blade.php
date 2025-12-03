@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Application Details' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="application-details-page">
			<div class="application-details-content">
				<h1 class="application-details-heading">@lang( 'Colombia Check-MIG Form' )</h1>
				<form
					id="application-details-form"
					action="{{ route( 'application.details.submit', [ 'country' => $country_slug ] ) }}"
					method="POST"
					x-data="{
						activeTravelers: Array.from({ length: {{ $applicants_count }} }, (_, i) => i + 1),
						maxTravelers: 10,
						nextTravelerIndex: {{ $applicants_count + 1 }},
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

			<!-- Info Card Sidebar -->
			<aside class="application-details-sidebar display-desktop-only">
				<div class="apply-info-card">
					<h3>@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h3>

					<div class="apply-info-items">
						<div class="apply-info-item">
							<div class="apply-info-icon">
								@include( 'icons.calendar' )
							</div>
							<div class="apply-info-details">
								<div class="apply-info-label">@lang( 'Valid for' )</div>
								<div class="apply-info-value">@lang( '90 days after arrival' )</div>
							</div>
						</div>

						<div class="apply-info-item">
							<div class="apply-info-icon">
								@include( 'icons.airplane-landing' )
							</div>
							<div class="apply-info-details">
								<div class="apply-info-label">@lang( 'Number of entries' )</div>
								<div class="apply-info-value">@lang( 'Single entry' )</div>
							</div>
						</div>

						<div class="apply-info-item">
							<div class="apply-info-icon">
								@include( 'icons.calendar-clock' )
							</div>
							<div class="apply-info-details">
								<div class="apply-info-label">@lang( 'Max stay' )</div>
								<div class="apply-info-value">@lang( '90 days per entry' )</div>
							</div>
						</div>
					</div>
				</div>
				<button
					type="submit"
					form="application-details-form"
					class="apply-submit-button mb-0"
					x-bind:aria-busy="isSubmitting"
				>
					@lang( 'Save and continue' )
				</button>
			</aside>
		</div>

		<!-- Mobile/Tablet Fixed Bottom Button -->
		<div class="apply-mobile-submit display-mobile-only">
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

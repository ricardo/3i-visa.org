@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Application Details' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="application-details-page">
			<div class="application-details-content">
				<h1 class="application-details-heading">
					@lang( 'Traveler information' )
				</h1>

				<form
					id="application-details-form"
					action="{{ route( 'application.details.submit', [ 'country' => $country_slug ] ) }}"
					method="POST"
					x-data="{
						activeTravelers: Array.from({ length: {{ $applicants_count }} }, (_, i) => i + 1),
						maxTravelers: 10,
						nextTravelerIndex: {{ $applicants_count + 1 }},
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
						}
					}"
					x-on:remove-traveler.window="removeTraveler($event.detail)"
				>
					@csrf

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
			>
				@lang( 'Save and continue' )
			</button>
		</div>
	</main>
@endsection

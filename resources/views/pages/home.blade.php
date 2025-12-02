@php
	// Limited country list for visa check.
	$visa_countries = [
		[ 'name' => 'Australia', 'code' => 'au' ],
		[ 'name' => 'Brazil', 'code' => 'br' ],
		[ 'name' => 'Canada', 'code' => 'ca' ],
		[ 'name' => 'Colombia', 'code' => 'co' ],
		[ 'name' => 'France', 'code' => 'fr' ],
		[ 'name' => 'Germany', 'code' => 'de' ],
		[ 'name' => 'United Kingdom', 'code' => 'gb' ],
		[ 'name' => 'United States', 'code' => 'us' ],
	];

	// Locale to country mapping for passport pre-selection.
	$locale_country_map = [
		'pt' => [ 'name' => 'Brazil', 'code' => 'br' ],
		'es' => [ 'name' => 'Colombia', 'code' => 'co' ],
		'en' => [ 'name' => 'United States', 'code' => 'us' ],
	];

	// Determine pre-selected passport country based on current locale.
	$current_locale = app()->getLocale();
	$pre_selected_passport = $locale_country_map[ $current_locale ] ?? $locale_country_map['en'];
@endphp
@extends( 'layouts/main' )
@section( 'title', __( 'Home' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container landing">
		<section class="hero medium">
			<h1 id="main-heading" class="color-highlight">
				@lang( 'The' ) <mark>@lang( 'easiest' )</mark> @lang( 'way to get your travel visa' )
			</h1>

			<form
				action="{{ route( 'visa-check' ) }}"
				method="POST"
				class="hero-form"
				x-data="{
					selectedPassport: {{ json_encode( $pre_selected_passport ) }},
					selectedDestination: null,
					passportError: false,
					destinationError: false,
					canSubmit: true,
					handlePassportSelection(event) {
						this.selectedPassport = event.detail.value;
						this.passportError = false;
						this.updateSubmitState();
					},
					handleDestinationSelection(event) {
						this.selectedDestination = event.detail.value;
						this.destinationError = false;
						this.updateSubmitState();
					},
					updateSubmitState() {
						this.canSubmit = this.selectedPassport && this.selectedDestination;
					},
					validateForm(event) {
						this.passportError = !this.selectedPassport;
						this.destinationError = !this.selectedDestination;

						if (this.passportError || this.destinationError) {
							event.preventDefault();
							return false;
						}
						return true;
					}
				}"
				x-on:item-selected.window="
					console.log('Event received:', $event.detail);
					if ($event.detail.wireModel === 'passport') {
						console.log('Passport selected');
						selectedPassport = $event.detail.value;
						passportError = false;
						updateSubmitState();
					} else if ($event.detail.wireModel === 'destination') {
						console.log('Destination selected');
						selectedDestination = $event.detail.value;
						destinationError = false;
						updateSubmitState();
					}
				"
				x-on:submit="validateForm($event)"
			>
				@csrf
				<div class="hero-form-inputs">
					<div class="hero-form-field">
						<label for="passport-input">@lang( 'My passport' )</label>
						<div x-bind:aria-invalid="passportError ? 'true' : null">
							@livewire( 'searchable-input', [
								'items' => $visa_countries,
								'placeholder' => __( 'Select your passport country' ),
								'show_flags' => true,
								'wire_model' => 'passport',
								'initial_value' => $pre_selected_passport,
								'required' => true,
							] )
						</div>
						<small class="error-message" x-show="passportError" x-transition>
							@lang( 'Please select your passport country' )
						</small>
						<input type="hidden" name="passport" x-bind:value="selectedPassport ? selectedPassport.code : ''" required>
					</div>

					<div class="hero-form-field">
						<label for="destination-input">@lang( 'My destination' )</label>
						<div x-bind:aria-invalid="destinationError ? 'true' : null">
							@livewire( 'searchable-input', [
								'items' => $visa_countries,
								'placeholder' => __( 'Traveling to' ),
								'show_flags' => true,
								'wire_model' => 'destination',
								'required' => true,
							] )
						</div>
						<small class="error-message" x-show="destinationError" x-transition>
							@lang( 'Please select your destination' )
						</small>
						<input type="hidden" name="destination" x-bind:value="selectedDestination ? selectedDestination.code : ''" required>
					</div>

					<div class="hero-form-field">
						<label>&nbsp;</label>
						<button
							type="submit"
							class="hero-submit-button mb-0"
						>
							@lang( 'Get started!' )
							@include('icons.arrow-right')
						</button>
					</div>
				</div>
			</form>
		</section>
		<br>
	</main>
@endsection

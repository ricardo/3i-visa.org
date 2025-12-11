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
@extends( 'layouts/main', [ 'class' => 'home' ] )
@section( 'title', __( 'International Visa Processing & Entry Requirements Portal' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="landing">
		<section class="hero medium container">
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
					passportError: {{ $errors->has('nationality') || $errors->has('passport') ? 'true' : 'false' }},
					destinationError: {{ $errors->has('destination') ? 'true' : 'false' }},
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
					if ($event.detail.wireModel === 'passport') {
						selectedPassport = $event.detail.value;
						passportError = false;
						updateSubmitState();
					} else if ($event.detail.wireModel === 'destination') {
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
							{{ $errors->first('nationality') ?: $errors->first('passport') ?: __( 'Please select your passport country' ) }}
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
							{{ $errors->first('destination') ?: __( 'Please select your destination' ) }}
						</small>
						<input type="hidden" name="destination" x-bind:value="selectedDestination ? selectedDestination.code : ''" required>
					</div>

					<div class="hero-form-field">
						<label class="hide-mobile">&nbsp;</label>
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

		<div class="home-image">
			<img class="traveler" src="{{ asset( 'images/traveler-on-suitcase-sm.webp' ) }}">
			<img class="line" src="{{ asset( 'images/line.webp' ) }}">
		</div>

		<section class="why-3i-visa container">
			<h2>@lang( 'Why millions of travelers use 3i-Visa' )</h2>

			<div class="grid">
				{{-- LEFT COLUMN --}}
				<div class="do-yourself">
					<h3>@lang( 'Do it yourself' )</h3>

					<ul>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'Confusing government websites, confusing forms, and confusing instructions' )
						</li>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'One mistake? Get rejected or delayed' )
						</li>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'Limited times when government will accept' )
						</li>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'Usually no assistance or support available' )
						</li>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'Start all over if you lose progress' )
						</li>
						<li>
							@include( 'icons.circle-minus' )
							@lang( 'Limited payment methods' )
						</li>
					</ul>
				</div>

				{{-- RIGHT COLUMN --}}
				<div class="with-3i-visa">
					<h3>
						@lang( 'With' )
						<img class="logo" src="{{ asset('images/logo.svg') }}" alt="3i-Visa">
					</h3>

					<ul>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Intuitive application, done in minutes' )
						</li>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Detailed application review ensures approval on the first try' )
						</li>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Apply anytime, 24/7' )
						</li>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Chat, WhatsApp, and email round-the-clock support' )
						</li>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Save and continue later' )
						</li>
						<li>
							@include( 'icons.circle-check-big' )
							@lang( 'Multiple payment options' )
						</li>
					</ul>

					<button class="button-primary" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
						@lang( 'Get Started' )
					</button>
				</div>
			</div>
		</section>

		<section class="stats-section">
			<div class="container">
				<div class="stats">
					<div class="stat">
						<div class="stat__number">99%</div>
						<div class="stat__label">@lang( 'worldwide approval rate' )</div>
					</div>

					<div class="stat">
						<div class="stat__number">10+</div>
						<div class="stat__label">@lang( 'years of experience' )</div>
					</div>

					<div class="stat">
						<div class="stat__number">24/7</div>
						<div class="stat__label">
							@lang( 'assistance in your language' )
						</div>
					</div>

					<div class="stat">
						<div class="stat__number">200+</div>
						<div class="stat__label">
							@lang( 'passport nationalities served' )
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="easy-process">
			<div class="container">

				<h2>@lang('Our easy process')</h2>

				<div class="easy-process__grid">

					<div class="easy-process__image">
						<img src="/images/easy-process.webp" alt="@lang('Travelers checking visa info')">
					</div>

					<div class="easy-process__steps">

						<div class="step">
							<div class="step__number">
								<span>01</span>
							</div>
							<div class="step__content">
								<h3>@lang('Find your visa')</h3>
								<p>@lang('Use our visa checker to see exactly what you need. No guesswork.')</p>
							</div>
						</div>

						<div class="step">
							<div class="step__number">
								<span>02</span>
							</div>
							<div class="step__content">
								<h3>@lang('Apply and pay in under 10 minutes')</h3>
								<p>@lang('Fill out your travel details and pay securely. Then, upload any other required docs.')</p>
							</div>
						</div>

						<div class="step">
							<div class="step__number">
								<span>03</span>
							</div>
							<div class="step__content">
								<h3>@lang('We\'ll handle the rest')</h3>
								<p>@lang('Our AI technology + human experts check everything to ensure a smooth approval.')</p>
							</div>
						</div>

					</div>

				</div>

			</div>
		</section>
	</main>
@endsection

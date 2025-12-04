@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Apply for :country Visa', [ 'country' => $country_name ] ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="apply-page">
			<div class="apply-content">
				<h1 class="apply-heading">
					@lang( 'Apply now for your :country Check-MIG Form', [ 'country' => $country_name ] )
				</h1>

				<!-- Hero Section -->
				<div class="apply-hero">
					<!--
						TODO: Add visual element here. Suggestions:
						- Airplane illustration with confetti/celebration elements
						- Animated airplane taking off with particle effects
						- Success checkmark icon with travel imagery
						- SVG illustration from undraw.co or similar
						- Lottie animation for dynamic effect
					-->
					<div class="apply-hero-content">
						<img src="{{ asset( 'images/plane.svg' ) }}">
						<h2>@lang( 'Good news! You don\'t need a visa!' )</h2>
					</div>
				</div>

				<!-- Application Form -->
				<form id="apply-form" action="{{ route( 'apply.submit', [ 'country' => $country_slug ] ) }}" method="POST" class="apply-form">
					@csrf

					<!-- Nationality Selection -->
					<div class="apply-form-field">
						<label>@lang( 'What\'s your nationality?' )</label>
						@livewire( 'searchable-input', [
							'items' => $visa_countries,
							'placeholder' => __( 'Select your nationality' ),
							'show_flags' => true,
							'wire_model' => 'nationality',
							'initial_value' => $selected_nationality,
							'required' => true,
						] )
						<input type="hidden" name="nationality" value="{{ $selected_nationality['code'] ?? '' }}" required>
						<small class="apply-helper-text">
							@lang( 'Ensure you select the nationality of the passport you\'ll be traveling with.' )
						</small>
					</div>

					<!-- Total Applicants -->
					<div class="apply-form-field">
						<label>@lang( 'Total applicants' )</label>
						<x-stepper name="applicants" :min="1" :max="10" :initial="(int) session('visa_application.applicants', 1)" />
					</div>
				</form>
			</div>

			<!-- Info Card Sidebar -->
			<aside class="apply-sidebar display-desktop-only">
				<div class="apply-info-card">
					<h3>@lang( ':country Check-MIG Form', [ 'country' => $country_name ] )</h3>

					<div class="apply-info-items">
						<div class="apply-info-item">
							<!-- TODO: Icon suggestion - Calendar icon for validity period -->
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
					form="apply-form"
					class="apply-submit-button mb-0"
				>
					@lang( 'Start your application' )
				</button>
			</aside>
		</div>

		<div class="apply-mobile-submit display-mobile-only">
			<button
				type="submit"
				form="apply-form"
				class="apply-mobile-submit-button"
			>
				@lang( 'Start your application' )
			</button>
		</div>
	</main>
@endsection

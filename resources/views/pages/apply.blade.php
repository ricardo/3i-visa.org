@extends( 'layouts/main' )
@section( 'title', __( 'Apply for :country :document', [ 'country' => $country_name, 'document' => __($document_type) ] ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="apply-page">
			<div class="apply-content">
				<h1 class="apply-heading">
					@lang( 'Apply now for your :country :document', [ 'country' => $country_name, 'document' => __($document_type) ] )
				</h1>

				<!-- Hero Section -->
				@if( ! $is_available )
					<!-- Not Available Message -->
					<div class="apply-hero">
						<div class="apply-hero-content">
							<p style="color: #64748b; font-size: 1.125rem; margin: 0;">
								@lang( 'A visa is required to travel to :country but 3i Visa cannot provide it at this time.', [ 'country' => $country_name ] )
							</p>
						</div>
					</div>
				@else
					<!-- Available Message -->
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
				@endif

				<!-- Application Form -->
				<form id="apply-form" action="{{ route( 'apply.submit', [ 'country' => $country_slug ] ) }}" method="POST" class="apply-form">
					@csrf

					<!-- Nationality Selection -->
					<div class="apply-form-field">
						<label>@lang( 'What\'s your nationality?' )</label>
						@if( ! $is_available )
							{{-- Disabled dropdown for not available --}}
							<div class="searchable-input" style="pointer-events: none;">
								<div class="searchable-input-trigger">
									@if( $selected_nationality )
										<span class="fi fi-{{ $selected_nationality['code'] }}" style="margin-right: 0.5rem;"></span>
										<span>{{ $selected_nationality['name'] }}</span>
									@endif
								</div>
							</div>
						@else
							@livewire( 'searchable-input', [
								'items' => $visa_countries,
								'placeholder' => __( 'Select your nationality' ),
								'show_flags' => true,
								'name' => 'nationality',
								'initial_value' => $selected_nationality,
								'required' => true,
							] )
						@endif
						<small class="apply-helper-text">
							@lang( 'Ensure you select the nationality of the passport you\'ll be traveling with.' )
						</small>
					</div>

					@if( $is_available )
						<!-- Total Applicants -->
						<div class="apply-form-field">
							<label>@lang( 'Total applicants' )</label>
							<x-stepper name="applicants" :min="1" :max="10" :initial="(int) session('visa_application.applicants', 1)" />
						</div>
					@endif
				</form>
			</div>

			<!-- Info Card Sidebar -->
			<aside class="apply-sidebar display-desktop-only">
				@if ( $is_available )
					<div class="apply-info-card">
						<h3>@lang( ':country :document', [ 'country' => $country_name, 'document' => __($document_type) ] )</h3>

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
				@else
					<a
						href="{{ route( 'home' ) }}"
						role="button"
						class="apply-submit-button mt-6 mb-0"
						style="display: block; text-align: center; text-decoration: none;"
					>
						@lang( 'Check Other Destinations' )
					</a>
				@endif
			</aside>
		</div>

		@if( $is_available )
			<div class="apply-mobile-submit display-mobile-only">
				<button
					type="submit"
					form="apply-form"
					class="apply-mobile-submit-button"
				>
					@lang( 'Start your application' )
				</button>
			</div>
		@else
			<div class="apply-mobile-submit display-mobile-only">
				<a
					href="{{ route( 'home' ) }}"
					role="button"
					class="apply-mobile-submit-button"
					style="display: block; text-align: center; text-decoration: none;"
				>
					@lang( 'Check Other Destinations' )
				</a>
			</div>
		@endif
	</main>
@endsection

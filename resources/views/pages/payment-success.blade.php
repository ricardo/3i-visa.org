@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Payment Successful' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="payment-success-page">
			<div class="payment-success-content">
				<!-- Success Hero -->
				<div class="success-hero text-center mb-7">
					<div class="success-icon mb-3">
						<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #28a745; margin: 0 auto;">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
					</div>
					<h1>@lang( 'Payment Successful!' )</h1>
					<p class="lead">@lang( 'Thank you for your order' )</p>

					@auth
						@if($is_new_payment)
							<!-- Welcome message for new users -->
							<div class="alert mt-5" style="background: #e3f2fd; border-color: #2196F3;">
								<p class="mb-0">
									<strong>@lang('Welcome!')</strong> @lang('Your account has been created. Check your email to set your password and access your orders anytime.')
								</p>
							</div>
						@endif
					@else
						@if($is_new_payment)
							<!-- Login prompt for existing users -->
							<div class="alert mt-5" style="background: #fff3cd; border-color: #ffc107;">
								<p class="mb-0">
									@lang('You already have an account with us.')
									<a href="{{ route('login') }}" style="text-decoration: underline; font-weight: 600;">@lang('Log in')</a>
									@lang('to view all your orders.')
								</p>
							</div>
						@endif
					@endauth
				</div>

				<!-- Order Selector (for users with multiple orders) -->
				@auth
					<x-order-selector
						:applications="$user_applications"
						:current_application_id="$application->id"
						:country_slug="$country_slug"
					/>
				@endauth

				<!-- Progress Steps -->
				<div class="progress-steps mb-7">
					@php
						// Determine current step based on application status
						$current_step = match($application->status) {
							'paid' => 2,
							'processing' => 2,
							'approved' => 3,
							'completed' => 3,
							default => 2,
						};
					@endphp

					<div class="progress-step" data-status="{{ $current_step >= 1 ? 'completed' : 'pending' }}">
						<div class="step-indicator">
							@if($current_step >= 1)
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							@else
								<span>1</span>
							@endif
						</div>
						<div class="step-content">
							<div class="step-title">@lang('Payment Successful')</div>
							<div class="step-description">@lang('Your payment has been processed')</div>
						</div>
					</div>

					<div class="progress-step" data-status="{{ $current_step >= 2 ? ($current_step == 2 ? 'active' : 'completed') : 'pending' }}">
						<div class="step-indicator">
							@if($current_step > 2)
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							@elseif($current_step == 2)
								<div class="step-spinner"></div>
							@else
								<span>2</span>
							@endif
						</div>
						<div class="step-content">
							<div class="step-title">@lang('Information Under Review')</div>
							<div class="step-description">@lang('We are reviewing your application details')</div>
						</div>
					</div>

					<div class="progress-step" data-status="{{ $current_step >= 3 ? 'completed' : 'pending' }}">
						<div class="step-indicator">
							@if($current_step >= 3)
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							@else
								<span>3</span>
							@endif
						</div>
						<div class="step-content">
							<div class="step-title">@lang('Check-MIG Form Ready')</div>
							<div class="step-description">@lang('Your approved form will be emailed to you')</div>
						</div>
					</div>
				</div>

				<!-- Order Details -->
				<article class="success-details">
					<h2>@lang( 'Order Details' )</h2>

					<div class="order-info mt-5">
						<div class="info-row">
							<span class="info-label">@lang( 'Order Number' ):</span>
							<span class="info-value"><strong>{{ $application->order_number }}</strong></span>
						</div>

						<div class="info-row">
							<span class="info-label">@lang( 'Destination' ):</span>
							<span class="info-value">{{ $country_name }}</span>
						</div>

						<div class="info-row">
							<span class="info-label">@lang( 'Number of Travelers' ):</span>
							<span class="info-value">{{ $application->number_of_travelers }}</span>
						</div>

						<div class="info-row">
							<span class="info-label">@lang( 'Processing Option' ):</span>
							<span class="info-value">{{ ucfirst( $application->processing_option ) }}</span>
						</div>

						<div class="info-row">
							<span class="info-label">@lang( 'Total Amount Paid' ):</span>
							<span class="info-value">
								<strong>
									@php
										echo \App\Helpers\Currencies::format( $total_amount, request()->cookie( 'preferred_currency', 'USD' ) );
									@endphp
								</strong>
							</span>
						</div>

						@if($application->expected_completion_date)
							<div class="info-row">
								<span class="info-label">@lang( 'Expected Completion' ):</span>
								<span class="info-value">{{ $application->expected_completion_date->format('M d, Y') }}</span>
							</div>
						@endif
					</div>

					<!-- Email Confirmation Notice -->
					<div class="alert mt-5">
						<p>
							@lang( 'A confirmation email has been sent to' )
							<strong>{{ $application->primary_contact_email }}</strong>.
						</p>
						<p class="mb-0">
							@lang( 'Your approved :country Check-MIG Form will be sent to this email address once processed.', [ 'country' => $country_name ] )
						</p>
					</div>

					<!-- Next Steps -->
					<div class="next-steps mt-5">
						<h3>@lang( 'What happens next?' )</h3>
						<ol>
							<li>@lang( 'We will review your application and passport details.' )</li>
							<li>@lang( 'Your visa will be processed within the selected timeframe.' )</li>
							<li>@lang( 'You will receive your approved visa via email.' )</li>
						</ol>
					</div>

					<!-- Action Buttons -->
					<div class="success-actions mt-7 text-center">
						<a href="{{ route( 'home' ) }}" class="contrast">
							@lang( 'Return to Home' )
						</a>
					</div>
				</article>
			</div>
		</div>
	</main>

	<style>
		.progress-steps {
			display: flex;
			flex-direction: column;
			gap: 24px;
			padding: 24px;
			background: #f8f9fa;
			border-radius: 12px;
		}

		.progress-step {
			display: flex;
			gap: 16px;
			align-items: flex-start;
			position: relative;
		}

		.progress-step:not(:last-child)::after {
			content: '';
			position: absolute;
			left: 19px;
			top: 48px;
			width: 2px;
			height: calc(100% + 24px);
			background: #dee2e6;
		}

		.progress-step[data-status="completed"]::after {
			background: #28a745;
		}

		.progress-step[data-status="active"]::after {
			background: linear-gradient(to bottom, #0066cc, #dee2e6);
		}

		.step-indicator {
			flex-shrink: 0;
			width: 40px;
			height: 40px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 600;
			font-size: 16px;
			position: relative;
			z-index: 1;
		}

		.progress-step[data-status="completed"] .step-indicator {
			background: #28a745;
			color: white;
		}

		.progress-step[data-status="active"] .step-indicator {
			background: #0066cc;
			color: white;
			box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.2);
		}

		.progress-step[data-status="pending"] .step-indicator {
			background: #dee2e6;
			color: #6c757d;
		}

		.step-content {
			flex: 1;
			padding-top: 4px;
		}

		.step-title {
			font-weight: 600;
			font-size: 16px;
			margin-bottom: 4px;
			color: #212529;
		}

		.progress-step[data-status="pending"] .step-title {
			color: #6c757d;
		}

		.step-description {
			font-size: 14px;
			color: #6c757d;
		}

		.step-spinner {
			width: 20px;
			height: 20px;
			border: 3px solid rgba(255, 255, 255, 0.3);
			border-top-color: white;
			border-radius: 50%;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			to { transform: rotate(360deg); }
		}

		@media (min-width: 768px) {
			.progress-steps {
				flex-direction: row;
				justify-content: space-between;
			}

			.progress-step {
				flex: 1;
				flex-direction: column;
				text-align: center;
				align-items: center;
			}

			.progress-step:not(:last-child)::after {
				left: calc(50% + 40px);
				top: 19px;
				width: calc(100% - 80px);
				height: 2px;
			}

			.step-content {
				padding-top: 12px;
			}
		}
	</style>
@endsection

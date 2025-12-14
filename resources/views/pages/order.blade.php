@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Payment Successful' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="order-page">
			<div class="order-content">
				<!-- Success Hero -->
				<div class="success-hero text-center mt-6 mb-7">
					@php
						// Determine current step title based on application status
						$step_title = match($application->status) {
							'paid' => __( 'Information Under Review' ),
							'processing' => __( 'Information Under Review' ),
							'approved' => __( 'Check-MIG Form Ready' ),
							'completed' => __( 'Check-MIG Form Ready' ),
							default => __( 'Pending Payment' ),
						};
					@endphp

					<h1 class="mb-2">
						{{ $step_title }}
					</h1>

					<p class="lead" style="color: var(--3i-muted-color); margin-bottom: 2rem;">
						@lang( 'Order Number' ): {{ $application->order_number }}
					</p>

					@if ( $payment_state === 'pending_payment' )
						<x-alert type="info">
							<strong>@lang( 'Payment pending' )</strong><br>
							@lang( 'We are confirming your payment with the provider. This usually takes a few minutes.' )
							<br>
							@lang( 'You will receive an email once the payment is confirmed.' )
						</x-alert>
					@endif

					@auth
						@if ( $is_new_payment )
							<x-alert type="info">
								<strong>@lang( 'Welcome!' )</strong> @lang( 'Your account has been created. Check your email to set your password and access your orders anytime.' )
							</x-alert>
						@endif
					@else
						@if ( $is_new_payment )
							<x-alert type="info">
								@lang( 'You already have an account with us.' )
								<a href="{{ route('login') }}" class="underline font-semibold">@lang( 'Log in' )</a>
								@lang('to view all your orders.')
							</x-alert>
						@endif
					@endauth
				</div>

				<!-- Progress Steps -->
				<div class="progress-steps mb-7">
					@php
						// Determine current step based on application status
						$current_step = match( $application->status ) {
							'paid' => 2,
							'processing' => 2,
							'approved' => 3,
							'completed' => 3,
							default => 1,
						};
					@endphp

					<div class="progress-step" data-status="{{ $current_step > 1 ? 'completed' : 'active' }}">
						<div class="step-indicator">
							@if($current_step > 1)
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							@else
								<div class="step-spinner"></div>
							@endif
						</div>
						<div class="step-content">
							@if ( $payment_state === 'pending_payment' )
								<div class="step-title">@lang( 'Pending Payment' )</div>
								<div class="step-description">@lang( 'Your payment is awaiting approval' )</div>
							@else
								<div class="step-title">@lang( 'Payment Successful' )</div>
								<div class="step-description">@lang( 'Your payment has been processed' )</div>
							@endif
						</div>
					</div>

					<div class="progress-step" data-status="{{ $current_step >= 2 ? ($current_step == 2 ? 'active' : 'completed') : 'pending' }}">
						<div class="step-indicator">
							@if ( $current_step > 2 )
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
									<polyline points="20 6 9 17 4 12"></polyline>
								</svg>
							@elseif ( $current_step == 2 )
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
							@if ( $current_step >= 3 )
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
				<div style="padding: 1rem;padding-bottom: 8rem;">
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
							<span class="info-value">@lang( ucfirst( $application->processing_option ) )</span>
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
								<span class="info-value">{{ $application->expected_completion_date->locale(app()->getLocale())->isoFormat('ll') }}</span>
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
					<div class="next-steps mt-7">
						<h3>@lang( 'What happens next?' )</h3>
						<ol>
							<li>@lang( 'We will review your application and passport details.' )</li>
							<li>@lang( 'Your visa will be processed within the selected timeframe.' )</li>
							<li>@lang( 'You will receive your approved visa via email.' )</li>
						</ol>
					</div>
				</div>
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
			border: 1px solid var( --3i-border-color );
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
			top: 40px;
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

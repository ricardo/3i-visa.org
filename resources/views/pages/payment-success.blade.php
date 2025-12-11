@extends( 'layouts/main', [ 'footer' => false ] )
@section( 'title', __( 'Payment Successful' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="payment-success-page">
			<div class="payment-success-content">
				<!-- Success Hero -->
				<div class="success-hero text-center mb-7">
					<div class="success-icon mb-3">
						<!-- You can add a success icon or animation here -->
						<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #28a745; margin: 0 auto;">
							<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
					</div>
					<h1>@lang( 'Payment Successful!' )</h1>
					<p class="lead">@lang( 'Thank you for your order' )</p>
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
@endsection

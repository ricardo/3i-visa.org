@extends( 'layouts/main' )
@section( 'title', __( 'My Orders' ) . ' • 3i Visa' )
@section( 'content' )
	<main class="container">
		<div class="account-page" style="max-width: 1000px; margin: 80px auto; padding: 1rem;">
			<h1 class="mb-6">@lang( 'My Orders' )</h1>

			@if($applications->isEmpty())
				<div style="text-align: center; padding: 3rem 1rem; background: var(--3i-background-color); border-radius: var(--3i-border-radius); border: 1px solid var(--3i-border-color);">
					<p style="color: var(--3i-muted-color); margin: 0;">
						@lang( 'You don\'t have any orders yet.' )
					</p>
					<a href="{{ route('home') }}" role="button" class="contrast" style="margin-top: 1.5rem; display: inline-block;">
						@lang( 'Start a new application' )
					</a>
				</div>
			@else
				<div class="orders-list">
					@foreach($applications as $application)
						@php
							$country_name = $application->destination_country_code ?
								\App\Helpers\Countries::getCountryName($application->destination_country_code) :
								__('Unknown');

							$status_labels = [
								'draft' => __('Draft'),
								'pending_payment' => __('Pending Payment'),
								'paid' => __('Payment Received'),
								'processing' => __('Processing'),
								'approved' => __('Approved'),
								'completed' => __('Completed'),
								'rejected' => __('Rejected'),
							];

							$status_colors = [
								'draft' => 'background: #f3f4f6; color: #374151;',
								'pending_payment' => 'background: #fef3c7; color: #92400e;',
								'paid' => 'background: #dbeafe; color: #1e40af;',
								'processing' => 'background: #dbeafe; color: #1e40af;',
								'approved' => 'background: #d1fae5; color: #065f46;',
								'completed' => 'background: #d1fae5; color: #065f46;',
								'rejected' => 'background: #fee2e2; color: #991b1b;',
							];

							$status_label = $status_labels[$application->status] ?? $application->status;
							$status_style = $status_colors[$application->status] ?? 'background: #f3f4f6; color: #374151;';
						@endphp

						<article class="order-card" style="border: 1px solid var(--3i-border-color); border-radius: var(--3i-border-radius); padding: 1.5rem; margin-bottom: 1rem;">
							<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1rem;">
								<div>
									<p style="font-size: 0.875rem; color: var(--3i-muted-color); margin: 0 0 0.25rem 0;">
										@lang( 'Order Number' )
									</p>
									<p style="font-weight: 600; margin: 0;">
										#{{ $application->order_number }}
									</p>
								</div>

								<div>
									<p style="font-size: 0.875rem; color: var(--3i-muted-color); margin: 0 0 0.25rem 0;">
										@lang( 'Destination' )
									</p>
									<p style="font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
										@if($application->destination_country_code)
											<span class="fi fi-{{ strtolower($application->destination_country_code) }}" style="font-size: 1.25rem;"></span>
										@endif
										{{ $country_name }}
									</p>
								</div>

								<div>
									<p style="font-size: 0.875rem; color: var(--3i-muted-color); margin: 0 0 0.25rem 0;">
										@lang( 'Status' )
									</p>
									<span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; {{ $status_style }}">
										{{ $status_label }}
									</span>
								</div>

								<div>
									<p style="font-size: 0.875rem; color: var(--3i-muted-color); margin: 0 0 0.25rem 0;">
										@lang( 'Date' )
									</p>
									<p style="font-weight: 600; margin: 0;">
										{{ $application->created_at->locale(app()->getLocale())->isoFormat('ll') }}
									</p>
								</div>
							</div>

							<div style="border-top: 1px solid var(--3i-border-color); padding-top: 1rem;">
								<a href="{{ route('order', ['order_number' => $application->order_number]) }}"
									role="button"
									class="contrast"
									style="display: inline-block; text-decoration: none; font-size: 0.9375rem;">
										@lang( 'View Details' ) →
								</a>
							</div>
						</article>
					@endforeach
				</div>
			@endif
		</div>
	</main>
@endsection

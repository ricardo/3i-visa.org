@props([
	'country_name' => 'Colombia',
	'applicants_count' => 1,
	'price_per_traveler' => 49,
	'currency_symbol' => '$',
])

@php
	$subtotal = $applicants_count * $price_per_traveler;
	$travelers_text = $applicants_count === 1 ? __('traveler') : __('travelers');
@endphp

<div class="order-summary-card">
	<div class="order-summary-row">
		<div class="order-summary-title">@lang(':country Check-MIG Form', ['country' => $country_name])</div>
		<div class="order-summary-value">{{ $applicants_count }} {{ $travelers_text }}</div>
	</div>
	<div class="order-summary-row">
		<div class="order-summary-label">@lang('Standard processing')</div>
		<div class="order-summary-value">{{ $currency_symbol }}{{ number_format($subtotal, 2) }}</div>
	</div>
</div>

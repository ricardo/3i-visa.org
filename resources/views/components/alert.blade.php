@php
	/**
	 * Alert Component
	 *
	 * @param string $type - 'error', 'success', or 'info'
	 * @param string|null $message - The alert message (optional if using slot)
	 */
	$type = $type ?? 'error';

	$styles = [
		'error' => [
			'bg' => '#FEE2E2',
			'color' => '#842029',
			'icon' => 'warning'
		],
		'success' => [
			'bg' => '#d1e7dd',
			'color' => '#0f5132',
			'icon' => 'check-circle'
		],
		'info' => [
			'bg' => '#cfe2ff',
			'color' => '#084298',
			'icon' => 'info'
		]
	];

	$style = $styles[$type] ?? $styles['error'];
@endphp

<div class="alert-banner" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }};">
	<div class="alert-banner-content">
		<div class="alert-banner-icon">
			@if($style['icon'] === 'warning')
				@include('icons.warning')
			@elseif($style['icon'] === 'check-circle')
				<svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
					<polyline points="22 4 12 14.01 9 11.01"></polyline>
				</svg>
			@elseif($style['icon'] === 'info')
				<svg xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10"></circle>
					<line x1="12" y1="16" x2="12" y2="12"></line>
					<line x1="12" y1="8" x2="12.01" y2="8"></line>
				</svg>
			@endif
		</div>
		<div>
			@if(isset($message) && $message)
				<strong>{{ $message }}</strong>
			@else
				{{ $slot }}
			@endif
		</div>
	</div>
</div>

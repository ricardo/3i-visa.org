@props(['current_step' => 2])

@php
	$steps = [
		1 => __('Trip details'),
		2 => __('Your info'),
		3 => __('Checkout'),
	];

	// Support decimal steps (e.g., 2.5 means between step 2 and 3)
	$current_step_floor = floor($current_step);
	$is_partial = $current_step != $current_step_floor;
@endphp

<div class="progress-steps">
	@foreach( $steps as $step_number => $step_label )
		@php
			// Determine step state
			if ( $step_number < $current_step_floor ) {
				$step_class = 'completed';
			} elseif ( $step_number == $current_step_floor ) {
				$step_class = 'current';
			} else {
				$step_class = 'pending';
			}
		@endphp

		<div class="progress-step {{ $step_class }}">
			<div class="progress-step-circle">
				@if ( $step_number < $current_step_floor )
					@include( 'icons.check' )
				@else
					<span>{{ $step_number }}</span>
				@endif
			</div>
			<div class="progress-step-label">{{ $step_label }}</div>
		</div>

		@if ( $step_number < count( $steps ) )
			@php
				// Determine connector state
				if ( $step_number < $current_step_floor ) {
					$connector_class = 'completed';
				} elseif ( $step_number == $current_step_floor && $is_partial ) {
					$connector_class = 'partial';
				} else {
					$connector_class = 'pending';
				}
			@endphp
			<div class="progress-step-connector {{ $connector_class }}"></div>
		@endif
	@endforeach
</div>

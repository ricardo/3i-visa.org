@props(['current_step' => 2])

@php
	$steps = [
		1 => __('Trip details'),
		2 => __('Your info'),
		3 => __('Checkout'),
	];
@endphp

<div class="progress-steps">
	@foreach($steps as $step_number => $step_label)
		<div class="progress-step {{ $step_number < $current_step ? 'completed' : ($step_number === $current_step ? 'current' : 'pending') }}">
			<div class="progress-step-circle">
				@if($step_number < $current_step)
					@include('icons.check')
				@else
					<span>{{ $step_number }}</span>
				@endif
			</div>
			<div class="progress-step-label">{{ $step_label }}</div>
		</div>
		@if($step_number < count($steps))
			<div class="progress-step-connector {{ $step_number < $current_step ? 'completed' : 'pending' }}"></div>
		@endif
	@endforeach
</div>

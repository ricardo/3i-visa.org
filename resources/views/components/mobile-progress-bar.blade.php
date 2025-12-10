@props( [ 'current_step' => 1, 'total_steps' => 2 ] )

@php
	// Show progress based on completed steps (step 2 means step 1 is completed = 33%)
	$progress_percentage = ( $current_step / $total_steps ) * 100;
@endphp

<div class="mobile-progress-bar">
	<div class="mobile-progress-bar-fill" style="width: {{ $progress_percentage }}%"></div>
</div>

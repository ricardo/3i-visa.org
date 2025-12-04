@props([
	'name' => 'date_of_birth',
	'traveler_index' => 1,
	'required' => true,
	'type' => 'birth',
	'initial_month' => '',
	'initial_day' => '',
	'initial_year' => ''
])

@php
	$current_year = date('Y');

	// Determine year range based on date type
	if ($type === 'expiration') {
		// Passport expiration: 2025 to 2045 (2025 + 20 years)
		$min_year = 2025;
		$max_year = 2025 + 20;
	} else {
		// Date of birth: current year down to 125 years ago
		$min_year = $current_year - 125;
		$max_year = $current_year;
	}

	$months = [
		1 => __('January'),
		2 => __('February'),
		3 => __('March'),
		4 => __('April'),
		5 => __('May'),
		6 => __('June'),
		7 => __('July'),
		8 => __('August'),
		9 => __('September'),
		10 => __('October'),
		11 => __('November'),
		12 => __('December'),
	];

	// Get values from old input or initial props
	$selected_month = old('travelers.'.$traveler_index.'.'.$name.'_month', $initial_month);
	$selected_day = old('travelers.'.$traveler_index.'.'.$name.'_day', $initial_day);
	$selected_year = old('travelers.'.$traveler_index.'.'.$name.'_year', $initial_year);
@endphp

<div class="date-selector">
	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_month]"
			x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.{{ $name }}_month') ? 'true' : null"
			x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.{{ $name }}_month') ? 'travelers-{{ $traveler_index }}-{{ $name }}-month-error' : null"
			x-on:change="clearFieldError('travelers.{{ $traveler_index }}.{{ $name }}_month')"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" {{ !$selected_month ? 'selected' : '' }}>@lang('Month')</option>
			@foreach($months as $value => $label)
				<option value="{{ $value }}" {{ $selected_month == $value ? 'selected' : '' }}>{{ $label }}</option>
			@endforeach
		</select>
		<small
			id="travelers-{{ $traveler_index }}-{{ $name }}-month-error"
			class="error-message"
			x-show="hasError('travelers.{{ $traveler_index }}.{{ $name }}_month')"
			x-text="getError('travelers.{{ $traveler_index }}.{{ $name }}_month')"
			x-transition
		></small>
	</div>

	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_day]"
			x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.{{ $name }}_day') ? 'true' : null"
			x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.{{ $name }}_day') ? 'travelers-{{ $traveler_index }}-{{ $name }}-day-error' : null"
			x-on:change="clearFieldError('travelers.{{ $traveler_index }}.{{ $name }}_day')"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" {{ !$selected_day ? 'selected' : '' }}>@lang('Day')</option>
			@for($day = 1; $day <= 31; $day++)
				<option value="{{ $day }}" {{ $selected_day == $day ? 'selected' : '' }}>{{ $day }}</option>
			@endfor
		</select>
		<small
			id="travelers-{{ $traveler_index }}-{{ $name }}-day-error"
			class="error-message"
			x-show="hasError('travelers.{{ $traveler_index }}.{{ $name }}_day')"
			x-text="getError('travelers.{{ $traveler_index }}.{{ $name }}_day')"
			x-transition
		></small>
	</div>

	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_year]"
			x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.{{ $name }}_year') ? 'true' : null"
			x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.{{ $name }}_year') ? 'travelers-{{ $traveler_index }}-{{ $name }}-year-error' : null"
			x-on:change="clearFieldError('travelers.{{ $traveler_index }}.{{ $name }}_year')"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" {{ !$selected_year ? 'selected' : '' }}>@lang('Year')</option>
			@if($type === 'expiration')
				{{-- Expiration dates: ascending order from min to max --}}
				@for($year = $min_year; $year <= $max_year; $year++)
					<option value="{{ $year }}" {{ $selected_year == $year ? 'selected' : '' }}>{{ $year }}</option>
				@endfor
			@else
				{{-- Birth dates: descending order from max to min --}}
				@for($year = $max_year; $year >= $min_year; $year--)
					<option value="{{ $year }}" {{ $selected_year == $year ? 'selected' : '' }}>{{ $year }}</option>
				@endfor
			@endif
		</select>
		<small
			id="travelers-{{ $traveler_index }}-{{ $name }}-year-error"
			class="error-message"
			x-show="hasError('travelers.{{ $traveler_index }}.{{ $name }}_year')"
			x-text="getError('travelers.{{ $traveler_index }}.{{ $name }}_year')"
			x-transition
		></small>
	</div>
</div>

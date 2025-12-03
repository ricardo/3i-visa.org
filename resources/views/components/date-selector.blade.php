@props(['name' => 'date_of_birth', 'traveler_index' => 1, 'required' => true])

@php
	$current_year = date('Y');
	$min_year = $current_year - 125;
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
@endphp

<div class="date-selector">
	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_month]"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" selected>@lang('Month')</option>
			@foreach($months as $value => $label)
				<option value="{{ $value }}">{{ $label }}</option>
			@endforeach
		</select>
	</div>

	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_day]"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" selected>@lang('Day')</option>
			@for($day = 1; $day <= 31; $day++)
				<option value="{{ $day }}">{{ $day }}</option>
			@endfor
		</select>
	</div>

	<div class="date-field">
		<select
			name="travelers[{{ $traveler_index }}][{{ $name }}_year]"
			{{ $required ? 'required' : '' }}
		>
			<option disabled value="" selected>@lang('Year')</option>
			@for($year = $current_year; $year >= $min_year; $year--)
				<option value="{{ $year }}">{{ $year }}</option>
			@endfor
		</select>
	</div>
</div>

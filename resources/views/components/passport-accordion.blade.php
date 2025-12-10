@props( [
	'traveler_index' => 1,
	'traveler_name' => '',
	'initial_expanded' => false,
	'initial_nationality' => null,
	'initial_passport_number' => '',
	'initial_passport_expiration_month' => '',
	'initial_passport_expiration_day' => '',
	'initial_passport_expiration_year' => '',
	'initial_add_passport_later' => false
] )

@php
	// Convert initial_nationality code to full country object for searchable-input
	$initial_country_value = null;
	if ( $initial_nationality ) {
		$all_countries = \App\Helpers\Countries::getCountries();
		foreach ( $all_countries as $country ) {
			if ( $country['code'] === $initial_nationality ) {
				$initial_country_value = $country;
				break;
			}
		}
	}
@endphp

<div
	class="traveler-accordion"
	x-bind:class="{ 'is-open': isOpen }"
	x-data="{
		isOpen: {{ $initial_expanded ? 'true' : 'false' }},
		travelerIndex: {{ $traveler_index }},
		sequentialNumber: {{ $traveler_index }},
		travelerName: '{{ $traveler_name }}',
		addPassportLater: {{ $initial_add_passport_later ? 'true' : 'false' }},
		storageKey: 'passport_details_accordions',
		get title() {
			return this.travelerName.trim()
				? '@lang('Traveler') #' + this.sequentialNumber + ' - ' + this.travelerName
				: '@lang('Traveler') #' + this.sequentialNumber;
		},
		init() {
			// Restore accordion state from sessionStorage
			const savedState = sessionStorage.getItem(this.storageKey);
			if (savedState) {
				try {
					const state = JSON.parse(savedState);
					if (state.hasOwnProperty(this.travelerIndex)) {
						this.isOpen = state[this.travelerIndex];
					}
				} catch (e) {
					// Invalid JSON, ignore
				}
			}
		},
		saveState() {
			// Save accordion state to sessionStorage
			try {
				const savedState = sessionStorage.getItem(this.storageKey);
				const state = savedState ? JSON.parse(savedState) : {};
				state[this.travelerIndex] = this.isOpen;
				sessionStorage.setItem(this.storageKey, JSON.stringify(state));
			} catch (e) {
				// sessionStorage not available, ignore
			}
		},
		toggleAccordion() {
			this.isOpen = !this.isOpen;
			this.saveState();
		},
		// Helper functions to access parent form data
		getFormData() {
			const form = this.$el.closest('form');
			return form ? Alpine.$data(form) : null;
		},
		hasError(field) {
			const formData = this.getFormData();
			return formData ? formData.hasError(field) : false;
		},
		getError(field) {
			const formData = this.getFormData();
			return formData ? formData.getError(field) : '';
		},
		clearFieldError(field) {
			const formData = this.getFormData();
			if (formData) {
				formData.clearFieldError(field);
			}
		}
	}"
	x-on:update-sequential-number.window="
		if ($event.detail.travelerIndex === travelerIndex) {
			sequentialNumber = $event.detail.sequentialNumber;
		}
	"
>
	<div class="traveler-accordion-header" x-on:click="toggleAccordion()">
		<h3 class="traveler-accordion-title" x-text="title"></h3>
		<div class="traveler-accordion-icon" x-bind:class="{ 'rotated': isOpen }">
			@include('icons.chevron-down')
		</div>
	</div>

	<div class="traveler-accordion-content" x-show="isOpen">
		<div class="traveler-accordion-body">
			<!-- Nationality on passport -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('Nationality on passport')</label>
				<div x-data="{ get isInvalid() { return hasError('travelers.{{ $traveler_index }}.nationality'); } }">
					<div x-bind:aria-invalid="isInvalid ? 'true' : null">
						<livewire:searchable-input
							:name="'travelers[' . $traveler_index . '][nationality]'"
							:items="\App\Helpers\Countries::getCountries()"
							:placeholder="__('Select your nationality')"
							:required="true"
							:show_flags="true"
							:initial_value="$initial_country_value"
						/>
					</div>
				</div>
				<small
					id="travelers-{{ $traveler_index }}-nationality-error"
					class="error-message"
					x-show="hasError('travelers.{{ $traveler_index }}.nationality')"
					x-text="getError('travelers.{{ $traveler_index }}.nationality')"
					x-transition
				></small>
			</div>

			<!-- Add passport details later checkbox -->
			<div class="traveler-field traveler-field-full mt-4">
				<label class="traveler-checkbox">
					<input
						type="checkbox"
						name="travelers[{{ $traveler_index }}][add_passport_later]"
						value="1"
						x-model="addPassportLater"
						x-on:change="
							if (addPassportLater) {
								clearFieldError('travelers.{{ $traveler_index }}.passport_number');
								clearFieldError('travelers.{{ $traveler_index }}.passport_expiration_month');
								clearFieldError('travelers.{{ $traveler_index }}.passport_expiration_day');
								clearFieldError('travelers.{{ $traveler_index }}.passport_expiration_year');
							}
						"
					>
					<span>@lang('Add passport details later')</span>
				</label>
			</div>

			<!-- Passport number (conditional) -->
			<div class="traveler-field traveler-field-full mt-4" x-show="!addPassportLater">
				<label>@lang('Passport number')</label>
				<input
					type="text"
					name="travelers[{{ $traveler_index }}][passport_number]"
					value="{{ old('travelers.'.$traveler_index.'.passport_number', $initial_passport_number) }}"
					x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.passport_number') ? 'true' : null"
					x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.passport_number') ? 'travelers-{{ $traveler_index }}-passport-number-error' : null"
					x-on:input="clearFieldError('travelers.{{ $traveler_index }}.passport_number')"
					placeholder="@lang('AB123456')"
				>
				<small
					id="travelers-{{ $traveler_index }}-passport-number-error"
					class="error-message"
					x-show="hasError('travelers.{{ $traveler_index }}.passport_number')"
					x-text="getError('travelers.{{ $traveler_index }}.passport_number')"
					x-transition
				></small>
			</div>

			<!-- Passport expiration date (conditional) -->
			<div class="traveler-field traveler-field-full" x-show="!addPassportLater">
				<label>@lang('Passport expiration date')</label>
				<x-date-selector
					:traveler_index="$traveler_index"
					name="passport_expiration"
					:required="false"
					type="expiration"
					:initial_month="$initial_passport_expiration_month"
					:initial_day="$initial_passport_expiration_day"
					:initial_year="$initial_passport_expiration_year"
				/>
			</div>
		</div>
	</div>
</div>

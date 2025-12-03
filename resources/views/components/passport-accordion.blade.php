@props( [ 'traveler_index' => 1, 'traveler_name' => '', 'initial_expanded' => false ] )

<div
	class="traveler-accordion"
	x-data="{
		isOpen: {{ $initial_expanded ? 'true' : 'false' }},
		travelerIndex: {{ $traveler_index }},
		sequentialNumber: {{ $traveler_index }},
		travelerName: '{{ $traveler_name }}',
		addPassportLater: false,
		get title() {
			return this.travelerName.trim()
				? '@lang('Traveler') #' + this.sequentialNumber + ' - ' + this.travelerName
				: '@lang('Traveler') #' + this.sequentialNumber;
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
	<div class="traveler-accordion-header" x-on:click="isOpen = !isOpen">
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
				<livewire:searchable-input
					:name="'travelers[' . $traveler_index . '][nationality]'"
					:items="\App\Helpers\Countries::getCountries()"
					:placeholder="__('Select your nationality')"
					:required="true"
				/>
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
				<x-date-selector :traveler_index="$traveler_index" name="passport_expiration" :required="false" />
			</div>
		</div>
	</div>
</div>

@props( [
	'traveler_index' => 1,
	'is_first' => false,
	'initial_expanded' => false,
	'initial_first_name' => '',
	'initial_last_name' => '',
	'initial_email' => '',
	'initial_dob_month' => '',
	'initial_dob_day' => '',
	'initial_dob_year' => '',
	'initial_marketing_optin' => false
] )

<div
	class="traveler-accordion"
	x-bind:class="{ 'is-open': isOpen }"
	x-data="{
		isOpen: {{ $initial_expanded ? 'true' : 'false' }},
		firstName: '{{ old('travelers.'.$traveler_index.'.first_name', $initial_first_name) }}',
		travelerIndex: {{ $traveler_index }},
		sequentialNumber: {{ $traveler_index }},
		storageKey: 'application_details_accordions',
		get title() {
			return this.firstName.trim()
				? '@lang('Traveler') #' + this.sequentialNumber + ' - ' + this.firstName
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
	x-on:expand-traveler.window="if ($event.detail === {{ $traveler_index }}) { isOpen = true; saveState(); }"
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
			<!-- First and middle name -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('First and middle name')</label>
				<input
					type="text"
					name="travelers[{{ $traveler_index }}][first_name]"
					x-model="firstName"
					x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.first_name') ? 'true' : null"
					x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.first_name') ? 'travelers-{{ $traveler_index }}-first-name-error' : null"
					x-on:input="clearFieldError('travelers.{{ $traveler_index }}.first_name')"
					placeholder="@lang('John William')"
				>
				<small
					id="travelers-{{ $traveler_index }}-first-name-error"
					class="error-message"
					x-show="hasError('travelers.{{ $traveler_index }}.first_name')"
					x-text="getError('travelers.{{ $traveler_index }}.first_name')"
					x-transition
				></small>
			</div>

			<!-- Last name -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('Last name')</label>
				<input
					type="text"
					name="travelers[{{ $traveler_index }}][last_name]"
					value="{{ old('travelers.'.$traveler_index.'.last_name', $initial_last_name) }}"
					x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.last_name') ? 'true' : null"
					x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.last_name') ? 'travelers-{{ $traveler_index }}-last-name-error' : null"
					x-on:input="clearFieldError('travelers.{{ $traveler_index }}.last_name')"
					placeholder="@lang('Smith')"
				>
				<small
					id="travelers-{{ $traveler_index }}-last-name-error"
					class="error-message"
					x-show="hasError('travelers.{{ $traveler_index }}.last_name')"
					x-text="getError('travelers.{{ $traveler_index }}.last_name')"
					x-transition
				></small>
			</div>

			<!-- Date of birth -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('Date of birth')</label>
				<x-date-selector
					:traveler_index="$traveler_index"
					name="date_of_birth"
					:required="false"
					:initial_month="$initial_dob_month"
					:initial_day="$initial_dob_day"
					:initial_year="$initial_dob_year"
				/>
			</div>

			@if ( $is_first )
				<!-- Email address (first traveler only) -->
				<div class="traveler-field traveler-field-full">
					<label>@lang('Email address')</label>
					<input
						type="email"
						name="travelers[{{ $traveler_index }}][email]"
						value="{{ old('travelers.'.$traveler_index.'.email', $initial_email) }}"
						x-bind:aria-invalid="hasError('travelers.{{ $traveler_index }}.email') ? 'true' : null"
						x-bind:aria-describedby="hasError('travelers.{{ $traveler_index }}.email') ? 'travelers-{{ $traveler_index }}-email-error' : null"
						x-on:input="clearFieldError('travelers.{{ $traveler_index }}.email')"
						placeholder="@lang('john@example.com')"
					>
					<small
						id="travelers-{{ $traveler_index }}-email-error"
						class="error-message"
						x-show="hasError('travelers.{{ $traveler_index }}.email')"
						x-text="getError('travelers.{{ $traveler_index }}.email')"
						x-transition
					></small>
					<small class="traveler-field-info">
						@lang('Your approved :country Check-MIG Form will be sent to this email address.', ['country' => session('visa_application.destination_name', 'Colombia')])
					</small>
				</div>

				<!-- Marketing opt-in checkbox (first traveler only) -->
				<div class="traveler-field traveler-field-full mt-5">
					<label class="traveler-checkbox">
						<input
							type="checkbox"
							name="travelers[{{ $traveler_index }}][marketing_optin]"
							value="1"
							{{ old('travelers.'.$traveler_index.'.marketing_optin', $initial_marketing_optin) ? 'checked' : '' }}
						>
						<span class="color-muted" style="font-weight: normal;font-size: .85rem">
							@lang('I want to receive 3i Visa updates, product launches and personalized offers. I can opt out anytime. Terms and Privacy Policy apply.')
						</span>
					</label>
				</div>
			@endif

			@if ( ! $is_first )
				<!-- Remove traveler link (travelers 2+) -->
				<div class="traveler-field traveler-field-full">
					<a
						href="#"
						role="button"
						class="traveler-remove-link"
						x-on:click.prevent="$dispatch('remove-traveler', {{ $traveler_index }})"
					>
						@include( 'icons.user-minus', [ 'class' => 'mr-2' ] )
						@lang('Remove this traveler')
					</a>
				</div>
			@endif
		</div>
	</div>
</div>

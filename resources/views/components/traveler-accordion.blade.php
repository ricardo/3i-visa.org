@props( [ 'traveler_index' => 1, 'is_first' => false, 'initial_expanded' => false ] )

<div
	class="traveler-accordion"
	x-data="{
		isOpen: {{ $initial_expanded ? 'true' : 'false' }},
		firstName: '',
		travelerIndex: {{ $traveler_index }},
		sequentialNumber: {{ $traveler_index }},
		get title() {
			return this.firstName.trim()
				? '@lang('Traveler') #' + this.sequentialNumber + ' - ' + this.firstName
				: '@lang('Traveler') #' + this.sequentialNumber;
		}
	}"
	x-on:expand-traveler.window="if ($event.detail === {{ $traveler_index }}) { isOpen = true; }"
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
			<!-- First and middle name -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('First and middle name')</label>
				<input
					type="text"
					name="travelers[{{ $traveler_index }}][first_name]"
					x-model="firstName"
					placeholder="@lang('John William')"
					required
				>
			</div>

			<!-- Last name -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('Last name')</label>
				<input
					type="text"
					name="travelers[{{ $traveler_index }}][last_name]"
					placeholder="@lang('Smith')"
					required
				>
			</div>

			<!-- Date of birth -->
			<div class="traveler-field traveler-field-full">
				<label>@lang('Date of birth')</label>
				<x-date-selector :traveler_index="$traveler_index" name="date_of_birth" :required="true" />
			</div>

			@if ( $is_first )
				<!-- Email address (first traveler only) -->
				<div class="traveler-field traveler-field-full">
					<label>@lang('Email address')</label>
					<input
						type="email"
						name="travelers[{{ $traveler_index }}][email]"
						class="mb-1"
						placeholder="@lang('john@example.com')"
						required
					>
					<small class="traveler-field-info">
						@lang('Your approved :country Check-MIG Form will be sent to this email address.', ['country' => session('visa_application.destination_name', 'Colombia')])
					</small>
				</div>

				<!-- Marketing opt-in checkbox (first traveler only) -->
				<div class="traveler-field traveler-field-full">
					<label class="traveler-checkbox">
						<input
							type="checkbox"
							name="travelers[{{ $traveler_index }}][marketing_optin]"
							value="1"
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

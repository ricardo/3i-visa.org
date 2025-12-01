@php
	$supported_locales = config( 'app.supported_locales', [] );
	$current_locale = app()->getLocale();
	$current_currency = request()->cookie( 'preferred_currency', 'USD' );

	// Map locales to country codes for flags
	$locale_flags = [
		'en' => 'us',
		'pt' => 'br',
		'es' => 'es',
	];

	// Prepare language items for searchable input
	$language_items = [];
	$current_language_item = null;
	foreach ( $supported_locales as $code => $name ) {
		$item = [
			'name' => $name,
			'code' => $locale_flags[$code] ?? $code,
			'locale' => $code,
		];
		$language_items[] = $item;
		if ( $code === $current_locale ) {
			$current_language_item = $item;
		}
	}

	// Get currencies
	$currencies = \App\Helpers\Currencies::getCurrencies();
	$current_currency_item = null;
	foreach ( $currencies as $currency ) {
		if ( $currency['code'] === $current_currency ) {
			$current_currency_item = $currency;
			break;
		}
	}
@endphp

<dialog id="dialog-language-switcher" class="dialog floating" aria-expanded="false" aria-label="Language and Currency Settings">
	<article id="language-switcher" class="small draggable">
		<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
		<div class="handle"></div>

		<h2>@lang( 'Language & Currency' )</h2>

		<div
			class="mt-7"
			x-data="{
				selectedLanguage: {{ json_encode( $current_language_item ) }},
				selectedCurrency: {{ json_encode( $current_currency_item ) }},
				savePreferences() {
					if ( this.selectedLanguage && this.selectedCurrency ) {
						document.querySelector( '#preferences-form input[name=locale]' ).value = this.selectedLanguage.locale;
						document.querySelector( '#preferences-form input[name=currency]' ).value = this.selectedCurrency.code;
						document.querySelector( '#preferences-form' ).submit();
					}
				}
			}"
			x-on:item-selected.window="
				if ( $event.detail.value ) {
					// Determine if it's a language or currency based on the presence of 'locale' field
					if ( $event.detail.value.locale ) {
						selectedLanguage = $event.detail.value;
					} else if ( $event.detail.value.code ) {
						selectedCurrency = $event.detail.value;
					}
				}
			"
		>
			<!-- Language Selection -->
			<div class="preference-section">
				<h3>@lang( 'Language' )</h3>
				@livewire( 'searchable-input', [
					'items' => $language_items,
					'placeholder' => __( 'Select language...' ),
					'show_flags' => true,
					'wire_model' => 'selected_language',
					'initial_value' => $current_language_item
				] )
			</div>

			<!-- Currency Selection -->
			<div class="preference-section mt-5">
				<h3>@lang( 'Currency' )</h3>
				@livewire( 'searchable-input', [
					'items' => $currencies,
					'placeholder' => __( 'Select currency...' ),
					'show_flags' => false,
					'wire_model' => 'selected_currency',
					'initial_value' => $current_currency_item
				] )
			</div>

			<!-- Save Button -->
			<div class="preference-actions mt-5">
				<button
					type="button"
					class="contrast mb-0"
					x-on:click="savePreferences()"
					x-bind:disabled="!selectedLanguage || !selectedCurrency"
				>
					@lang( 'Save preferences' )
				</button>
			</div>

			<!-- Hidden form for preferences submission -->
			<form id="preferences-form" method="POST" action="{{ route( 'preferences.save' ) }}" style="display: none;">
				@csrf
				<input type="hidden" name="current_route" value="{{ Route::currentRouteName() }}">
				<input type="hidden" name="route_params" value="{{ json_encode( Route::current()?->parameters() ) }}">
				<input type="hidden" name="locale" value="">
				<input type="hidden" name="currency" value="">
			</form>
		</div>
	</article>
</dialog>

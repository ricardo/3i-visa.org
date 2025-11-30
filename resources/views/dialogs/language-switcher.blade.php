@php
	$supported_locales = config( 'app.supported_locales', [] );
	$current_locale = app()->getLocale();

	// Map locales to country codes for flags
	$locale_flags = [
		'en' => 'us',
		'pt' => 'br',
		'es' => 'es',
	];

	// Prepare language items for searchable input
	$language_items = [];
	foreach ( $supported_locales as $code => $name ) {
		$language_items[] = [
			'name' => $name,
			'code' => $locale_flags[$code] ?? $code,
			'locale' => $code,
		];
	}

	// Get currencies
	$currencies = \App\Helpers\Currencies::getCurrencies();
@endphp

<dialog id="dialog-language-switcher" class="dialog floating" aria-expanded="false" aria-label="Language and Currency Settings">
	<article id="language-switcher" class="small draggable">
		<a href="javascript: void(0);" aria-label="@lang( 'Close' )" class="close icon-link"></a>
		<div class="handle"></div>

		<h2>@lang( 'Language & Currency' )</h2>

		<div class="mt-7">
			<!-- Language Selection -->
			<div class="preference-section">
				<h3>@lang( 'Language' )</h3>
				<div
					x-data="{ selectedLanguage: null }"
					x-on:item-selected.window="
						if ( $event.detail.value && $event.detail.value.locale ) {
							// Submit language change form
							document.querySelector( '#language-switch-form input[name=locale]' ).value = $event.detail.value.locale;
							document.querySelector( '#language-switch-form' ).submit();
						}
					"
				>
					@livewire( 'searchable-input', [
						'items' => $language_items,
						'placeholder' => __( 'Select language...' ),
						'show_flags' => true,
						'wire_model' => 'selected_language'
					] )
				</div>
			</div>

			<!-- Currency Selection -->
			<div class="preference-section mt-5">
				<h3>@lang( 'Currency' )</h3>
				<div
					x-data="{ selectedCurrency: null }"
					x-on:item-selected.window="
						if ( $event.detail.value && $event.detail.value.code ) {
							selectedCurrency = $event.detail.value;
							// Store currency preference (you can implement this via AJAX or local storage)
							localStorage.setItem( 'preferred_currency', $event.detail.value.code );
							console.log( 'Currency selected:', $event.detail.value );
						}
					"
				>
					@livewire( 'searchable-input', [
						'items' => $currencies,
						'placeholder' => __( 'Select currency...' ),
						'show_flags' => false,
						'wire_model' => 'selected_currency'
					] )
				</div>
			</div>

			<!-- Hidden form for language switching -->
			<form id="language-switch-form" method="POST" action="{{ route( 'language.switch' ) }}" style="display: none;">
				@csrf
				<input type="hidden" name="current_route" value="{{ Route::currentRouteName() }}">
				<input type="hidden" name="route_params" value="{{ json_encode( Route::current()?->parameters() ) }}">
				<input type="hidden" name="locale" value="">
			</form>
		</div>
	</article>
</dialog>

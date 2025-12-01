@if ( session( 'toast' ) )
	@push( 'scripts' )
		<script>
			document.addEventListener( 'DOMContentLoaded', () => {
				toast( '{{ session( 'toast' ) }}', '{{ session( 'toast_type', 'info' ) }}', {{ session( 'toast_duration' ) ?? 'null' }} );
			} );
		</script>
	@endpush
@endif

{{-- For livewire events --}}
@push( 'scripts' )
	<script>
		document.addEventListener( 'toast', function ( event ) {
			toast( event.detail[0].message );
		} );
	</script>
	{{-- Fix Livewire bug from Laravel 11 --}}
	<script>
		( function() {
			const segment = window.location.pathname.split('/')[1];
			const localePrefix = (segment && segment !== 'en') ? `/${segment}` : '';

			// Wait for DOM to load so we can modify the tag before Livewire boots
			document.addEventListener( 'DOMContentLoaded', function() {
				const lwScript = document.querySelector( 'script[src*="livewire"][data-update-uri]' );
				if ( ! lwScript ) {
					return;
				}

				const original = lwScript.getAttribute( 'data-update-uri' );
				const updated = localePrefix + original;

				lwScript.setAttribute( 'data-update-uri', updated );
			} );
		} )();
	</script>
@endpush

@if ( isset( $errors ) && $errors->any() )
	@push( 'scripts' )
		<script>
			document.addEventListener( 'DOMContentLoaded', () => {
				const offset = 20;
				const $invalidElement = $( '[aria-invalid="true"]' ).first();

				if ( $invalidElement.length ) {
					const elementOffset = $invalidElement.offset().top - offset;
					$( 'html, body' ).animate(
						{ scrollTop: elementOffset },
						500
					);
				}
			} );
		</script>
	@endpush
@endif

@stack( 'scripts' )
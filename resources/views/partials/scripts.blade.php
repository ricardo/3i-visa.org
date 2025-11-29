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
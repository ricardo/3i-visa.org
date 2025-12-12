import $ from 'jquery';

export default function initDropdown() {
	// When clicking a .dropdown, close all other dropdowns.
	$( document ).on( 'click', '.dropdown', function ( e ) {
		e.stopPropagation();
		$( '.dropdown' ).not( this ).attr( 'open', false );
	} );

	// When hovering over a .dropdown with .hoverable class, open it.
	$( document ).on( 'mouseenter', '.dropdown.hoverable', function ( e ) {
		const timer = $.data( this, 'closeTimer' );
		if ( timer ) {
			clearTimeout( timer );
			$.removeData( this, 'closeTimer' );
		}
		$( this ).attr( 'open', true );
	} );

	// When leaving a .dropdown with .hoverable class, close it.
	$( document ).on( 'mouseleave', '.dropdown.hoverable', function ( e ) {
		const el = this;
		const timer = setTimeout( function () {
			$( el ).attr( 'open', false );
			$.removeData( el, 'closeTimer' );
		}, 500 );
		$.data( el, 'closeTimer', timer );
	} );

	$( document ).on( 'pointerdown', function ( e ) {
		if ( $( e.target ).closest( '.dropdown' ).length === 0 ) {
			$( '.dropdown[open]' ).prop( 'open', false );
		}
	} );

	// Prevent toggling closed if already open from hover
	$( document ).on( 'click', '.dropdown.hoverable > summary', function ( e ) {
		const $details = $( this ).parent();

		// If it was opened by hover, keep it open
		if ( $details.is( '[open]' ) ) {
			e.preventDefault(); // stop default toggle
		}
	} );
}

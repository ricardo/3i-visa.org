import $ from 'jquery';

export default function initMobileMenu() {
	const $mobileMenu = $( '.mobile-menu' );
	const $menuToggle = $( '#menu-toggle' );
	const $menuClose = $( '.menu-close' );

	// Open menu
	$menuToggle.on( 'click', function() {
		$mobileMenu.addClass( 'open' );
		$( 'body' ).addClass( 'lock-scroll' );
	} );

	// Close menu
	function closeMenu() {
		$mobileMenu.removeClass( 'open' );
		$( 'body' ).removeClass( 'lock-scroll' );
	}

	$menuClose.on( 'click', closeMenu );

	// Close on escape key
	$( document ).on( 'keydown', function( e ) {
		if ( e.key === 'Escape' && $mobileMenu.hasClass( 'open' ) ) {
			closeMenu();
		}
	} );

	// Close menu when clicking on a menu item that opens a dialog
	$( '.mobile-menu-item' ).on( 'click', function() {
		// Small delay to allow the dialog to open first
		setTimeout( closeMenu, 100 );
	} );
}

import $ from 'jquery';

export default function initDialog() {
	window.openDialog = function ( dialog, article ) {
		const $dialog = $( dialog ).filter( '.dialog' ); // Ensure it's a an app dialog.

		if ( ! $dialog.length ) {
			return;
		}

		if ( article ) {
			// If an article is specified, show it.
			$dialog.find( 'article' ).hide();
			$dialog.find( article ).show();
		}

		if ( $dialog.attr( 'open' ) !== 'open' ) {
			// If the dialog is not open, open it.
			$dialog.attr( 'open', true );
		}

		// Prevent overscrolling.
		$( 'html' ).addClass( 'no-overscroll' );
		// Lock body scroll.
		$( 'body' ).addClass( 'lock-scroll' );
	};

	window.closeDialog = function ( $dialog, force = false ) {
		$dialog = $( $dialog ).filter( '.dialog' ); // Ensure it's a an app dialog.

		if ( ! $dialog.length ) {
			return;
		}

		// Make the above a foreach.
		for ( const dialog of $dialog ) {
			if ( ! force && dialog.hasAttribute( 'data-keep-open' ) ) {
				continue;
			}

			const $d = $( dialog );

			// Prevent re-entrancy and only close if actually open.
			if ( $d.hasClass( 'is-closing' ) || $d.attr( 'open' ) !== 'open' ) {
				continue;
			}

			const finalize = function () {
				$d.off( 'transitionend.regdlg' );
				$d.removeClass( 'is-closing' );
				$d.removeAttr( 'open' );

				// Reset/cleanup articles
				$d.find( 'article' ).each( function () {
					const $article = $( this );
					$article.hide();
					$article.css( {
						'transition' : '',
						'animation'  : '',
						'transform'  : '',
					} );
				} );

				// If no dialogs left open, restore scrolling
				if ( $( '.dialog[open]' ).length === 0 ) {
					$( 'html' ).removeClass( 'no-overscroll' );
					$( 'body' ).removeClass( 'lock-scroll' );
				}
			};

			const onEnd = function ( e ) {
				// Only when the dialog container finishes its own transition/animation
				if ( e.target === dialog ) {
					finalize();
				}
			};

			$d.on( 'transitionend.regdlg', onEnd );
			$d.addClass( 'is-closing' );

			// Fallback in case transitionend doesn’t fire (edge cases)
			setTimeout( finalize, 333 );
		}


		// Check if there are any open dialogs left.
		if ( $( '.dialog[open]' ).length === 0 ) {
			// If no dialogs are open, remove the no-overscroll class.
			$( 'html' ).removeClass( 'no-overscroll' );
			// Unlock body scroll.
			$( 'body' ).removeClass( 'lock-scroll' );
		}
	};

	// Close self when clicking the close button.
	$( '.dialog .close, .dialog [data-close="true"]' ).on( 'click', function ( e ) {
		e.preventDefault();
		const $dialog = $( this ).closest( '.dialog' );
		closeDialog( $dialog );
	} );

	// Close all when pressing Escape key.
	$( document ).on( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closeDialog( $( '.dialog' ) );
		}
	} );

	let startY = 0;
	let currentY = 0;
	let isDragging = false;

	const DRAG_THRESHOLD = 140;

	// Close when clicking outside the dialog.
	$( '.dialog' ).on( 'click', function ( e ) {
		if ( isDragging ) {
			// Prevent closing while dragging.
			return;
		}

		if ( e.target === this ) {
			closeDialog( $( this ) );
		}
	} );

	// Close when dragging down.
	$( '.dialog article.draggable' ).on( 'mousedown touchstart', function ( ev ) {
		const $article = $( ev.target ).closest( 'article' );

		if ( $article.scrollTop() > 0 ) {
			return;
		}

		// Start dragging only if the handle is visible (small screens).
		if ( $article.find( '.handle' ).css( 'display' ) === 'none' ) {
			return;
		}

		const event  = ev.type === 'touchstart' ? ev.originalEvent.touches[0] : ev;
		const $modal = $article.closest( '.dialog' );

		startY     = event.clientY;
		isDragging = true;
		currentY   = 0;

		$article.css( 'transition', 'none' );
		// Remove animation to prevent flickering.
		$article.css( 'animation', 'none' );

		// Find initial backdrop filter and opacity.
		const initialOpacity = parseFloat( $modal.css( 'background-color' ).match(/rgba\(\s*0,\s*0,\s*0,\s*([0-9.]+)\s*\)/)[1] );
		const initialBlur = parseFloat( $modal.css( 'backdrop-filter' ).match(/blur\(\s*([0-9.]+)px\s*\)/)[1] );

		$( document ).on( 'mousemove.dialog touchmove.dialog', function ( e ) {

			if ( ! isDragging ) {
				return;
			}

			const moveEvent = e.type === 'touchmove' ? e.originalEvent.touches[0] : e;
			currentY        = moveEvent.clientY - startY;

			// Only allow dragging downward.
			if ( currentY < 0 ) {
				return;
			}

			$article.css( 'transform', `translateY(${currentY}px)` );

			// Update backdrop opacity.
			const opacity = Math.max( 0, 1 - currentY / 300 );
			$modal.css( 'background-color', `rgba( 0, 0, 0, ${opacity * initialOpacity}px )` );
			$modal.css( 'backdrop-filter', `blur( ${opacity * initialBlur}px )` );
		} );

		$( document ).on( 'mouseup.dialog touchend.dialog', function () {
			$( document ).off( '.dialog' );

			setTimeout( () => {
				isDragging = false;
			} );

			if ( currentY > DRAG_THRESHOLD ) {
				closeDialog( $modal );
			}

			$article.css( {
				'transition' : 'transform .2s ease',
				'transform'  : 'translateY(0)',
			} );

			$modal.css( 'background-color', `rgba( 0, 0, 0, ${initialOpacity} )` );
			$modal.css( 'backdrop-filter', `blur( ${initialBlur}px )` );
		} );
	} );
};

/**
 * Get It Framed NI -- front-end behaviour.
 *
 * Plain JavaScript, no framework and no build step. Everything here is an
 * enhancement: with scripts blocked the menu is still reachable, every card is
 * still a link, and the gallery simply shows everything.
 */
( function () {
	'use strict';

	/* Mobile menu ------------------------------------------------------- */
	var toggle = document.querySelector( '.mobile-toggle' );
	var nav    = document.getElementById( 'mainNav' );

	if ( toggle && nav ) {
		toggle.addEventListener( 'click', function () {
			var open = nav.classList.toggle( 'open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}

	/* Services dropdown, tap to open on small screens ------------------- */
	document.querySelectorAll( '.nav-dropdown > a' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( event ) {
			if ( window.innerWidth <= 768 ) {
				event.preventDefault();
				link.parentElement.classList.toggle( 'open' );
			}
		} );
	} );

	/* Whole service card clickable -------------------------------------- */
	document.querySelectorAll( '.ga-card[data-href]' ).forEach( function ( card ) {
		card.addEventListener( 'click', function ( event ) {
			// Let a real link, or a text selection, behave normally.
			if ( event.target.closest( 'a' ) ) {
				return;
			}
			if ( window.getSelection && window.getSelection().toString() ) {
				return;
			}
			window.location.href = card.getAttribute( 'data-href' );
		} );
	} );

	/* Gallery filtering -------------------------------------------------- */
	var filters = document.querySelectorAll( '.gallery-filter' );
	var items   = document.querySelectorAll( '.gallery-item' );
	var empty   = document.getElementById( 'galleryEmpty' );

	if ( filters.length && items.length ) {
		filters.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var wanted = button.getAttribute( 'data-filter' );
				var shown  = 0;

				filters.forEach( function ( other ) {
					other.classList.toggle( 'active', other === button );
				} );

				items.forEach( function ( item ) {
					var cats  = ( item.getAttribute( 'data-cats' ) || '' ).split( ' ' );
					var match = 'all' === wanted || cats.indexOf( wanted ) !== -1;
					item.hidden = ! match;
					if ( match ) {
						shown++;
					}
				} );

				if ( empty ) {
					empty.hidden = shown > 0;
				}
			} );
		} );
	}

	/* Lightbox ----------------------------------------------------------- */
	var box     = document.getElementById( 'gifLightbox' );
	var boxImg  = document.getElementById( 'gifLightboxImg' );
	var boxCap  = document.getElementById( 'gifLightboxCaption' );
	var lastFocus = null;

	function closeBox() {
		if ( ! box ) {
			return;
		}
		box.classList.remove( 'open' );
		boxImg.src = '';
		if ( lastFocus ) {
			lastFocus.focus();
		}
	}

	if ( box && boxImg ) {
		items.forEach( function ( item ) {
			var full = item.getAttribute( 'data-full' );
			if ( ! full ) {
				return;
			}
			item.setAttribute( 'tabindex', '0' );
			item.setAttribute( 'role', 'button' );

			function open() {
				lastFocus  = item;
				boxImg.src = full;
				boxImg.alt = item.getAttribute( 'data-caption' ) || '';
				if ( boxCap ) {
					boxCap.textContent = item.getAttribute( 'data-caption' ) || '';
				}
				box.classList.add( 'open' );
				box.querySelector( '.lightbox-close' ).focus();
			}

			item.addEventListener( 'click', open );
			item.addEventListener( 'keydown', function ( event ) {
				if ( 'Enter' === event.key || ' ' === event.key ) {
					event.preventDefault();
					open();
				}
			} );
		} );

		box.addEventListener( 'click', function ( event ) {
			if ( event.target === box || event.target.closest( '.lightbox-close' ) ) {
				closeBox();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				closeBox();
			}
		} );
	}
}() );

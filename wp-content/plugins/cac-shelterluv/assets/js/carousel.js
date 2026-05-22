/**
 * CAC ShelterLuv – Carousel behaviour
 *
 * Uses CSS scroll-snap for the actual snapping; this script only handles
 * the prev/next buttons, disabled-state updates, and keyboard arrow support.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.pets-carousel' ).forEach( function ( carousel ) {
		var track   = carousel.querySelector( '.pets-carousel__track' );
		var prevBtn = carousel.querySelector( '.pets-carousel__btn--prev' );
		var nextBtn = carousel.querySelector( '.pets-carousel__btn--next' );

		if ( ! track ) return;

		/** Width of one card including its right gap. */
		function slideWidth() {
			var card = track.querySelector( '.pet-card' );
			if ( ! card ) return track.offsetWidth;
			var style  = getComputedStyle( track );
			var gap    = parseFloat( style.columnGap || style.gap || '0' );
			return card.offsetWidth + gap;
		}

		/** How many whole cards are visible at once. */
		function visibleCount() {
			var card = track.querySelector( '.pet-card' );
			if ( ! card ) return 1;
			return Math.max( 1, Math.round( track.offsetWidth / card.offsetWidth ) );
		}

		function scrollBy( direction ) {
			track.scrollBy( {
				left: direction * slideWidth() * visibleCount(),
				behavior: 'smooth',
			} );
		}

		function updateButtons() {
			var atStart = track.scrollLeft <= 1;
			var atEnd   = track.scrollLeft + track.offsetWidth >= track.scrollWidth - 1;

			if ( prevBtn ) prevBtn.disabled = atStart;
			if ( nextBtn ) nextBtn.disabled = atEnd;
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () { scrollBy( -1 ); } );
		}

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () { scrollBy( 1 ); } );
		}

		// Keyboard left/right when focus is inside the carousel.
		carousel.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowLeft' )  { scrollBy( -1 ); e.preventDefault(); }
			if ( e.key === 'ArrowRight' ) { scrollBy( 1 );  e.preventDefault(); }
		} );

		track.addEventListener( 'scroll', updateButtons, { passive: true } );

		// Re-evaluate on resize (visible count may change).
		var resizeTimer;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( updateButtons, 150 );
		} );

		updateButtons();
	} );
} )();

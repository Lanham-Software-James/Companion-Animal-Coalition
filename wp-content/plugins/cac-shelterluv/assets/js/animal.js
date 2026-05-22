( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var mainPhoto = document.getElementById( 'cac-sl-main-photo' );
        var thumbs    = document.querySelectorAll( '.cac-sl-animal__thumb' );

        if ( ! mainPhoto || ! thumbs.length ) return;

        thumbs.forEach( function ( thumb ) {
            thumb.addEventListener( 'click', function () {
                var src = this.getAttribute( 'data-src' );
                if ( ! src || src === mainPhoto.src ) return;

                // Fade out, swap, fade in.
                mainPhoto.classList.add( 'is-loading' );
                var img   = new Image();
                img.onload = function () {
                    mainPhoto.src = src;
                    mainPhoto.classList.remove( 'is-loading' );
                };
                img.src = src;

                // Update active state.
                thumbs.forEach( function ( t ) {
                    t.classList.remove( 'is-active' );
                    t.setAttribute( 'aria-pressed', 'false' );
                } );
                thumb.classList.add( 'is-active' );
                thumb.setAttribute( 'aria-pressed', 'true' );
            } );
        } );
    } );
} )();

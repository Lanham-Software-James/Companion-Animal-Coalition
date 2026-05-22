( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var form = document.querySelector( '.cac-sl-filters' );
        if ( ! form ) return;

        // Hide the submit button — changes auto-submit via JS.
        form.classList.add( 'cac-sl-filters--js' );

        // Filter selects (type, sex, age) carry data-filter; sort select does not.
        var filterSelects = form.querySelectorAll( 'select[data-filter]' );
        var allSelects    = form.querySelectorAll( 'select' );

        function markActive( select ) {
            select.classList.toggle( 'is-active', select.value !== '' );
        }

        allSelects.forEach( function ( select ) {
            markActive( select );
            select.addEventListener( 'change', function () {
                markActive( this );
                form.submit();
            } );
        } );
    } );
} )();

( function () {
  'use strict';

  // ── Mobile Navigation ──────────────────────────────────────
  const toggle = document.getElementById( 'mobile-menu-toggle' );
  const mobileNav = document.getElementById( 'mobile-nav' );

  if ( toggle && mobileNav ) {
    toggle.addEventListener( 'click', function () {
      const isOpen = toggle.getAttribute( 'aria-expanded' ) === 'true';

      toggle.setAttribute( 'aria-expanded', String( ! isOpen ) );
      toggle.setAttribute(
        'aria-label',
        isOpen ? 'Open navigation menu' : 'Close navigation menu'
      );

      if ( isOpen ) {
        mobileNav.setAttribute( 'aria-hidden', 'true' );
        // Delay hidden attr to allow CSS transition to finish
        setTimeout( () => mobileNav.setAttribute( 'hidden', '' ), 350 );
      } else {
        mobileNav.removeAttribute( 'hidden' );
        // rAF ensures the element is visible before transition starts
        requestAnimationFrame( () =>
          mobileNav.setAttribute( 'aria-hidden', 'false' )
        );
      }
    } );

    // Close on Escape
    document.addEventListener( 'keydown', function ( e ) {
      if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
        toggle.setAttribute( 'aria-expanded', 'false' );
        toggle.setAttribute( 'aria-label', 'Open navigation menu' );
        mobileNav.setAttribute( 'aria-hidden', 'true' );
        setTimeout( () => mobileNav.setAttribute( 'hidden', '' ), 350 );
        toggle.focus();
      }
    } );

    // Close when focus leaves the nav
    mobileNav.addEventListener( 'focusout', function ( e ) {
      if (
        toggle.getAttribute( 'aria-expanded' ) === 'true' &&
        ! mobileNav.contains( e.relatedTarget ) &&
        e.relatedTarget !== toggle
      ) {
        toggle.setAttribute( 'aria-expanded', 'false' );
        toggle.setAttribute( 'aria-label', 'Open navigation menu' );
        mobileNav.setAttribute( 'aria-hidden', 'true' );
        setTimeout( () => mobileNav.setAttribute( 'hidden', '' ), 350 );
      }
    } );
  }

  // ── Desktop Nav: keyboard-accessible sub-menus ─────────────
  const navItems = document.querySelectorAll( '.site-nav__list > .menu-item-has-children' );

  navItems.forEach( function ( item ) {
    const link = item.querySelector( ':scope > a' );
    const subMenu = item.querySelector( '.sub-menu' );
    if ( ! link || ! subMenu ) return;

    // Toggle sub-menu on Enter/Space when focused via keyboard
    link.addEventListener( 'keydown', function ( e ) {
      if ( e.key === 'Enter' || e.key === ' ' ) {
        e.preventDefault();
        const isExpanded = link.getAttribute( 'aria-expanded' ) === 'true';
        closeAllSubMenus();
        if ( ! isExpanded ) {
          link.setAttribute( 'aria-expanded', 'true' );
          subMenu.removeAttribute( 'hidden' );
        }
      }
      if ( e.key === 'Escape' ) {
        link.setAttribute( 'aria-expanded', 'false' );
        link.focus();
      }
    } );

    // Close when focus leaves the item
    item.addEventListener( 'focusout', function ( e ) {
      if ( ! item.contains( e.relatedTarget ) ) {
        link.setAttribute( 'aria-expanded', 'false' );
      }
    } );
  } );

  function closeAllSubMenus() {
    navItems.forEach( function ( item ) {
      const link = item.querySelector( ':scope > a' );
      if ( link ) link.setAttribute( 'aria-expanded', 'false' );
    } );
  }

  document.addEventListener( 'keydown', function ( e ) {
    if ( e.key === 'Escape' ) closeAllSubMenus();
  } );

  // ── Impact Stats Counter Animation ────────────────────────
  const counters = document.querySelectorAll( '.js-counter' );
  if ( counters.length === 0 ) return;

  // Only run if the user hasn't requested reduced motion
  const prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

  if ( prefersReducedMotion ) return;

  const observer = new IntersectionObserver(
    function ( entries, obs ) {
      entries.forEach( function ( entry ) {
        if ( ! entry.isIntersecting ) return;
        obs.unobserve( entry.target );
        animateCounter( entry.target );
      } );
    },
    { threshold: 0.5 }
  );

  counters.forEach( function ( el ) {
    observer.observe( el );
  } );

  function animateCounter( el ) {
    const raw = el.getAttribute( 'data-target' ) || el.textContent.trim();
    // Extract the numeric portion (handles "6,800+" or "18,000+")
    const suffix = raw.replace( /[\d,]/g, '' ).trim(); // e.g. "+"
    const numericStr = raw.replace( /[^\d]/g, '' );   // e.g. "6800"
    const target = parseInt( numericStr, 10 );

    if ( isNaN( target ) ) return;

    const duration = 1600;
    const start = performance.now();

    function easeOut( t ) {
      return 1 - Math.pow( 1 - t, 3 );
    }

    function tick( now ) {
      const elapsed = Math.min( now - start, duration );
      const progress = easeOut( elapsed / duration );
      const value = Math.round( progress * target );

      el.textContent = value.toLocaleString() + suffix;

      if ( elapsed < duration ) {
        requestAnimationFrame( tick );
      } else {
        el.textContent = raw; // Restore exact original string
      }
    }

    requestAnimationFrame( tick );
  }
} )();

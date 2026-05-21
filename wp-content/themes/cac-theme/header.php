<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link sr-only" href="#main-content">
    <?php esc_html_e( 'Skip to main content', 'cac-theme' ); ?>
</a>

<header class="site-header" role="banner">
    <div class="site-header__inner container">

        <!-- Logo / Site Identity -->
        <div class="site-header__brand">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-header__site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                    <?php bloginfo( 'name' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Primary Navigation (desktop) -->
        <nav class="site-nav" id="site-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'cac-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'menu_class'     => 'site-nav__list',
                'container'      => false,
                'fallback_cb'    => 'cac_nav_fallback',
                'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
                'depth'          => 2,
            ] );
            ?>
        </nav>

        <!-- Donate CTA + Mobile Toggle -->
        <div class="site-header__actions">
            <a href="<?php echo esc_url( get_theme_mod( 'cac_hero_cta_donate_url', '/donate' ) ); ?>"
               class="btn btn--primary btn--donate"
               aria-label="<?php esc_attr_e( 'Donate to Companion Animal Coalition', 'cac-theme' ); ?>">
                <?php esc_html_e( 'Donate', 'cac-theme' ); ?>
                <svg aria-hidden="true" focusable="false" class="btn__icon" viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </a>

            <button class="mobile-menu-toggle"
                    id="mobile-menu-toggle"
                    aria-controls="mobile-nav"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e( 'Open navigation menu', 'cac-theme' ); ?>">
                <span class="mobile-menu-toggle__bar" aria-hidden="true"></span>
                <span class="mobile-menu-toggle__bar" aria-hidden="true"></span>
                <span class="mobile-menu-toggle__bar" aria-hidden="true"></span>
            </button>
        </div>

    </div><!-- .site-header__inner -->

    <!-- Mobile Navigation Drawer -->
    <nav class="mobile-nav"
         id="mobile-nav"
         aria-label="<?php esc_attr_e( 'Mobile navigation', 'cac-theme' ); ?>"
         aria-hidden="true"
         hidden>
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary',
            'menu_id'        => 'mobile-menu',
            'menu_class'     => 'mobile-nav__list',
            'container'      => false,
            'fallback_cb'    => false,
            'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
            'depth'          => 2,
        ] );
        ?>
        <div class="mobile-nav__cta">
            <a href="<?php echo esc_url( get_theme_mod( 'cac_hero_cta_adopt_url', '/adopt' ) ); ?>"
               class="btn btn--primary btn--full">
                <?php esc_html_e( 'Adopt a Pet', 'cac-theme' ); ?>
            </a>
            <a href="<?php echo esc_url( get_theme_mod( 'cac_hero_cta_donate_url', '/donate' ) ); ?>"
               class="btn btn--outline-white btn--full">
                <?php esc_html_e( 'Donate Today', 'cac-theme' ); ?>
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </a>
        </div>
    </nav>
</header>
<?php
function cac_nav_fallback() {
    if ( current_user_can( 'manage_options' ) ) {
        printf(
            '<p class="nav-fallback"><a href="%s">%s</a></p>',
            esc_url( admin_url( 'nav-menus.php' ) ),
            esc_html__( 'Add a navigation menu', 'cac-theme' )
        );
    }
}

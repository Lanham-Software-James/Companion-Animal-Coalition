<footer class="site-footer" role="contentinfo">
    <div class="site-footer__main">
        <div class="container">
            <div class="site-footer__grid">

                <!-- Brand Column -->
                <div class="site-footer__brand">
                    <?php if ( has_custom_logo() ) : ?>
                        <div class="site-footer__logo site-footer__logo--inverted">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <p class="site-footer__site-name"><?php bloginfo( 'name' ); ?></p>
                    <?php endif; ?>
                    <p class="site-footer__tagline">
                        <?php esc_html_e( 'Rescue. Rehabilitate. Rehome. Repeat.', 'cac-theme' ); ?>
                    </p>
                    <p class="site-footer__description">
                        <?php esc_html_e( 'Building a community where every companion animal is valued, protected, and given the chance to thrive.', 'cac-theme' ); ?>
                    </p>
                    <div class="site-footer__social" aria-label="<?php esc_attr_e( 'Social media links', 'cac-theme' ); ?>">
                        <a href="#" class="site-footer__social-link" aria-label="<?php esc_attr_e( 'Facebook', 'cac-theme' ); ?>">
                            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                        </a>
                        <a href="#" class="site-footer__social-link" aria-label="<?php esc_attr_e( 'Instagram', 'cac-theme' ); ?>">
                            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                        </a>
                        <a href="#" class="site-footer__social-link" aria-label="<?php esc_attr_e( 'TikTok', 'cac-theme' ); ?>">
                            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V9.31a8.16 8.16 0 0 0 4.77 1.52V7.38a4.85 4.85 0 0 1-1-.69z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Nav Column: About -->
                <div class="site-footer__nav-col">
                    <h3 class="site-footer__nav-heading"><?php esc_html_e( 'About', 'cac-theme' ); ?></h3>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer-1',
                        'menu_class'     => 'site-footer__nav-list',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ] );
                    ?>
                </div>

                <!-- Nav Column: Get Involved -->
                <div class="site-footer__nav-col">
                    <h3 class="site-footer__nav-heading"><?php esc_html_e( 'Get Involved', 'cac-theme' ); ?></h3>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer-2',
                        'menu_class'     => 'site-footer__nav-list',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ] );
                    ?>
                </div>

                <!-- Nav Column: Resources -->
                <div class="site-footer__nav-col">
                    <h3 class="site-footer__nav-heading"><?php esc_html_e( 'Resources', 'cac-theme' ); ?></h3>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer-3',
                        'menu_class'     => 'site-footer__nav-list',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => false,
                    ] );
                    ?>
                </div>

            </div><!-- .site-footer__grid -->
        </div><!-- .container -->
    </div><!-- .site-footer__main -->

    <div class="site-footer__bottom">
        <div class="container">
            <p class="site-footer__copyright">
                &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'All rights reserved.', 'cac-theme' ); ?>
            </p>
            <p class="site-footer__legal">
                <a href="/privacy-policy"><?php esc_html_e( 'Privacy Policy', 'cac-theme' ); ?></a>
                <span aria-hidden="true">&middot;</span>
                <a href="/terms"><?php esc_html_e( 'Terms of Use', 'cac-theme' ); ?></a>
                <span aria-hidden="true">&middot;</span>
                <a href="/accessibility"><?php esc_html_e( 'Accessibility', 'cac-theme' ); ?></a>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

<section class="adoptable-pets" aria-labelledby="adoptable-pets-heading">
    <div class="container">

        <div class="section-header">
            <h2 class="section-heading" id="adoptable-pets-heading">
                <?php esc_html_e( 'Meet Some of Our Adoptable Pets', 'cac-theme' ); ?>
            </h2>
            <a href="<?php echo esc_url( get_theme_mod( 'cac_hero_cta_adopt_url', '/adopt' ) ); ?>"
               class="section-header__link" aria-label="<?php esc_attr_e( 'View all adoptable pets', 'cac-theme' ); ?>">
                <?php esc_html_e( 'View All Pets', 'cac-theme' ); ?>
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

        <?php
        /**
         * Placeholder: The adoptable pets grid is populated by the CAC ShelterLuv Plugin.
         * Install and activate the plugin (wp-content/plugins/cac-shelterlove) to display
         * live animals from ShelterLuv. The plugin registers the [cac_adoptable_pets] shortcode
         * and the `cac_adoptable_pets` action hook used below.
         */
        if ( has_action( 'cac_adoptable_pets_grid' ) ) :
            do_action( 'cac_adoptable_pets_grid' );
        else : ?>
            <div class="pets-placeholder" role="status" aria-live="polite">
                <div class="pets-placeholder__inner">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="48" height="48">
                        <ellipse cx="16" cy="12" rx="4" ry="5"/>
                        <ellipse cx="32" cy="12" rx="4" ry="5"/>
                        <ellipse cx="9" cy="22" rx="3.5" ry="4.5"/>
                        <ellipse cx="39" cy="22" rx="3.5" ry="4.5"/>
                        <path d="M24 18c-8 0-14 6-11 14 1.5 3.5 5 6 11 6s9.5-2.5 11-6c3-8-3-14-11-14z"/>
                    </svg>
                    <h3 class="pets-placeholder__title">
                        <?php esc_html_e( 'Adoptable Pets Coming Soon', 'cac-theme' ); ?>
                    </h3>
                    <p class="pets-placeholder__description">
                        <?php esc_html_e( 'Install the CAC ShelterLuv Plugin to display live adoptable animals from your ShelterLuv account.', 'cac-theme' ); ?>
                    </p>
                    <?php if ( current_user_can( 'activate_plugins' ) ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="btn btn--primary">
                            <?php esc_html_e( 'Manage Plugins', 'cac-theme' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

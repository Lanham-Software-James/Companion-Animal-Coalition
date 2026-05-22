<?php
/**
 * Homepage adoptable-pets carousel.
 *
 * Hooks into the cac_adoptable_pets_grid action fired by the CAC theme's
 * template-parts/home/adoptable-pets.php template.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Carousel {

    const DEFAULT_FETCH_COUNT = 8;

    private CAC_ShelterLuv_API $api;

    public function __construct( CAC_ShelterLuv_API $api ) {
        $this->api = $api;

        add_action( 'cac_adoptable_pets_grid', [ $this, 'render' ] );
        add_action( 'wp_enqueue_scripts',      [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets(): void {
        if ( ! is_front_page() ) {
            return;
        }

        wp_enqueue_style(
            'cac-shelterluv-carousel',
            CAC_SL_URL . 'assets/css/carousel.css',
            [ 'cac-main' ],
            CAC_SL_VERSION
        );

        wp_enqueue_script(
            'cac-shelterluv-carousel',
            CAC_SL_URL . 'assets/js/carousel.js',
            [],
            CAC_SL_VERSION,
            [ 'in_footer' => true, 'strategy' => 'defer' ]
        );
    }

    public function render(): void {
        $limit   = (int) get_option( 'cac_shelterluv_fetch_count', self::DEFAULT_FETCH_COUNT );
        $animals = $this->api->get_animals( max( 1, min( 100, $limit ) ) );

        if ( is_wp_error( $animals ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                printf(
                    '<p class="cac-sl-admin-notice">%s</p>',
                    esc_html( $animals->get_error_message() )
                );
            }
            return;
        }

        if ( empty( $animals ) ) {
            echo '<p class="cac-sl-empty">' . esc_html__( 'No adoptable pets are listed right now — check back soon!', 'cac-shelterluv' ) . '</p>';
            return;
        }

        $count = count( $animals );
        ?>
        <div class="pets-carousel" role="region" aria-label="<?php esc_attr_e( 'Adoptable Pets', 'cac-shelterluv' ); ?>">

            <?php if ( $count > 4 ) : ?>
            <button
                class="pets-carousel__btn pets-carousel__btn--prev"
                type="button"
                aria-label="<?php esc_attr_e( 'Previous pets', 'cac-shelterluv' ); ?>"
                disabled
            >
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>
            <?php endif; ?>

            <div class="pets-carousel__track">
                <?php foreach ( $animals as $animal ) : ?>
                    <?php CAC_ShelterLuv_Card::render( $animal ); ?>
                <?php endforeach; ?>
            </div>

            <?php if ( $count > 4 ) : ?>
            <button
                class="pets-carousel__btn pets-carousel__btn--next"
                type="button"
                aria-label="<?php esc_attr_e( 'Next pets', 'cac-shelterluv' ); ?>"
            >
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
            <?php endif; ?>

        </div>
        <?php
    }

}

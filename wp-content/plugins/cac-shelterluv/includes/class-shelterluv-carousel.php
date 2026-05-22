<?php
/**
 * Homepage adoptable-pets carousel.
 *
 * Hooks into the cac_adoptable_pets_grid action fired by the CAC theme's
 * template-parts/home/adoptable-pets.php template.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Carousel {

    /** Number of animals to request. JS/CSS shows 4 at a time; extras allow paging. */
    const FETCH_COUNT = 8;

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
        $animals = $this->api->get_animals( self::FETCH_COUNT );

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
                    <?php $this->render_card( $animal ); ?>
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

    private function render_card( array $animal ): void {
        $name      = $animal['Name']         ?? '';
        $type      = $animal['Type']         ?? '';
        $sex       = $animal['Sex']          ?? '';
        $age_raw   = $animal['Age']          ?? null;
        $photos    = $animal['Photos']       ?? [];
        $photo_url = ! empty( $photos ) ? $photos[0] : '';
        $profile   = $animal['ProfileUrl']   ?? '';

        $age_label = $this->format_age( is_numeric( $age_raw ) ? (int) $age_raw : null );

        $card_label = $name
            /* translators: %s: pet name */
            ? sprintf( __( "View %s's adoption profile", 'cac-shelterluv' ), $name )
            : __( 'View adoption profile', 'cac-shelterluv' );
        ?>
        <article class="pet-card">
            <a
                href="<?php echo $profile ? esc_url( $profile ) : '#'; ?>"
                class="pet-card__link"
                aria-label="<?php echo esc_attr( $card_label ); ?>"
                <?php echo $profile ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
            >
                <?php if ( $photo_url ) : ?>
                    <img
                        class="pet-card__image"
                        src="<?php echo esc_url( $photo_url ); ?>"
                        alt="<?php echo esc_attr( $name ); ?>"
                        loading="lazy"
                        decoding="async"
                        width="400"
                        height="300"
                    />
                <?php else : ?>
                    <div class="pet-card__image pet-card__image--placeholder" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40" aria-hidden="true" focusable="false">
                            <ellipse cx="16" cy="12" rx="4" ry="5"/>
                            <ellipse cx="32" cy="12" rx="4" ry="5"/>
                            <ellipse cx="9"  cy="22" rx="3.5" ry="4.5"/>
                            <ellipse cx="39" cy="22" rx="3.5" ry="4.5"/>
                            <path d="M24 18c-8 0-14 6-11 14 1.5 3.5 5 6 11 6s9.5-2.5 11-6c3-8-3-14-11-14z"/>
                        </svg>
                    </div>
                <?php endif; ?>

                <div class="pet-card__body">
                    <h3 class="pet-card__name"><?php echo esc_html( $name ); ?></h3>
                    <div class="pet-card__meta">
                        <?php if ( $age_label ) : ?><span><?php echo esc_html( $age_label ); ?></span><?php endif; ?>
                        <?php if ( $sex )       : ?><span><?php echo esc_html( ucfirst( $sex ) ); ?></span><?php endif; ?>
                        <?php if ( $type )      : ?><span><?php echo esc_html( $type ); ?></span><?php endif; ?>
                    </div>
                </div>
            </a>
        </article>
        <?php
    }

    /** Convert ShelterLuv's age-in-months integer to a human-readable string. */
    private function format_age( ?int $months ): string {
        if ( null === $months || $months < 0 ) {
            return '';
        }
        if ( $months < 12 ) {
            return sprintf(
                /* translators: %d: age in months */
                _n( '%d month old', '%d months old', $months, 'cac-shelterluv' ),
                $months
            );
        }
        $years = (int) round( $months / 12 );
        return sprintf(
            /* translators: %d: age in years */
            _n( '%d year old', '%d years old', $years, 'cac-shelterluv' ),
            $years
        );
    }
}

<?php
/**
 * Shared pet-card renderer.
 *
 * Called by both the carousel and the full listing so card markup stays
 * in one place.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Card {

    public static function render( array $animal ): void {
        $name        = $animal['Name']        ?? '';
        $type        = $animal['Type']        ?? '';
        $sex         = $animal['Sex']         ?? '';
        $age_raw     = $animal['Age']         ?? null;
        $photos      = $animal['Photos']      ?? [];
        $photo_url   = ! empty( $photos ) ? $photos[0] : '';
        $internal_id = $animal['Internal-ID'] ?? '';

        $age_label = self::format_age( is_numeric( $age_raw ) ? (int) $age_raw : null );

        $card_label = $name
            /* translators: %s: pet name */
            ? sprintf( __( "View %s's profile", 'cac-shelterluv' ), $name )
            : __( 'View profile', 'cac-shelterluv' );

        $detail_page_id = (int) get_option( 'cac_shelterluv_detail_page_id', 0 );
        if ( $detail_page_id && $internal_id ) {
            $href        = add_query_arg( 'animal_id', $internal_id, get_permalink( $detail_page_id ) );
            $link_extras = '';
        } else {
            $href        = '#';
            $link_extras = '';
        }
        ?>
        <article class="pet-card">
            <a
                href="<?php echo esc_url( $href ); ?>"
                class="pet-card__link"
                aria-label="<?php echo esc_attr( $card_label ); ?>"
                <?php echo $link_extras; ?>
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
    public static function format_age( ?int $months ): string {
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

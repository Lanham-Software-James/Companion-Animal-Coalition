<?php
/**
 * Single animal detail page.
 *
 * Shortcode: [cac_animal_detail]
 *
 * Reads ?animal_id=<Internal-ID> from the URL, fetches the animal from the
 * ShelterLuv API, and renders a full detail view with photo gallery.
 *
 * Setup:
 *  1. Create a WordPress page (e.g. "Animal Profile").
 *  2. Add [cac_animal_detail] to its content.
 *  3. In Settings › ShelterLuv, set "Animal Detail Page" to that page.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Animal_Detail {

    private CAC_ShelterLuv_API $api;

    public function __construct( CAC_ShelterLuv_API $api ) {
        $this->api = $api;
        add_shortcode( 'cac_animal_detail', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts',   [ $this, 'enqueue_assets' ] );
        add_filter( 'document_title_parts', [ $this, 'filter_page_title' ] );
    }

    public function enqueue_assets(): void {
        if ( ! $this->is_detail_page() ) {
            return;
        }

        wp_enqueue_style(
            'cac-shelterluv-animal',
            CAC_SL_URL . 'assets/css/animal.css',
            [ 'cac-main' ],
            CAC_SL_VERSION
        );

        wp_enqueue_script(
            'cac-shelterluv-animal',
            CAC_SL_URL . 'assets/js/animal.js',
            [],
            CAC_SL_VERSION,
            [ 'in_footer' => true, 'strategy' => 'defer' ]
        );
    }

    private function is_detail_page(): bool {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cac_animal_detail' );
    }

    /** Update the browser tab title to the animal's name. */
    public function filter_page_title( array $parts ): array {
        if ( ! $this->is_detail_page() ) {
            return $parts;
        }
        $internal_id = sanitize_text_field( wp_unslash( $_GET['animal_id'] ?? '' ) );
        if ( ! $internal_id ) {
            return $parts;
        }
        $animal = $this->api->get_animal( $internal_id );
        if ( ! is_wp_error( $animal ) && ! empty( $animal['Name'] ) ) {
            $parts['title'] = $animal['Name'];
        }
        return $parts;
    }

    public function render_shortcode(): string {
        $internal_id = sanitize_text_field( wp_unslash( $_GET['animal_id'] ?? '' ) );

        ob_start();

        if ( ! $internal_id ) {
            echo '<p class="cac-sl-empty">' . esc_html__( 'No animal specified.', 'cac-shelterluv' ) . '</p>';
            return ob_get_clean();
        }

        $animal = $this->api->get_animal( $internal_id );

        if ( is_wp_error( $animal ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                printf(
                    '<p class="cac-sl-admin-notice">%s</p>',
                    esc_html( $animal->get_error_message() )
                );
            } else {
                echo '<p class="cac-sl-empty">' . esc_html__( 'This pet could not be found.', 'cac-shelterluv' ) . '</p>';
            }
            return ob_get_clean();
        }

        $this->render_animal( $animal );
        return ob_get_clean();
    }

    // ── Render ──────────────────────────────────────────────────────────────

    private function render_animal( array $animal ): void {
        $name        = $animal['Name']               ?? '';
        $type        = $animal['Type']               ?? '';
        $breed       = $animal['Breed']              ?? '';
        $sex         = $animal['Sex']                ?? '';
        $age_raw     = $animal['Age']                ?? null;
        $color       = $animal['Color']              ?? '';
        $pattern     = $animal['Pattern']            ?? '';
        $size        = $animal['Size']               ?? '';
        $altered     = $animal['Altered']            ?? '';
        $weight      = $animal['CurrentWeightPounds'] ?? '';
        $description = $animal['Description']        ?? '';
        $status      = $animal['Status']             ?? '';
        $in_foster   = $animal['InFoster']           ?? false;
        $photos      = $animal['Photos']             ?? [];
        $attributes  = $animal['Attributes']         ?? [];
        $intake_ts   = (int) ( $animal['LastIntakeUnixTime'] ?? 0 );
        $fee_group   = $animal['AdoptionFeeGroup']   ?? null;

        $age_label   = CAC_ShelterLuv_Card::format_age( is_numeric( $age_raw ) ? (int) $age_raw : null );
        $adopt_url   = (string) get_option( 'cac_shelterluv_adopt_url', '' );

        $days_in_care = $intake_ts > 0
            ? max( 0, (int) floor( ( time() - $intake_ts ) / DAY_IN_SECONDS ) )
            : null;

        $pub_attributes = array_filter(
            $attributes,
            fn( $a ) => isset( $a['Publish'] ) && 'Yes' === $a['Publish']
        );

        // Back-to-listing link.
        $listing_page_id = (int) get_option( 'cac_shelterluv_listing_page_id', 0 );
        $listing_url     = $listing_page_id ? get_permalink( $listing_page_id ) : '';

        // Adoption fee display.
        $fee_display = '';
        if ( is_array( $fee_group ) && isset( $fee_group['Price'] ) ) {
            $price    = (float) $fee_group['Price'];
            $discount = (float) ( $fee_group['Discount'] ?? 0 );
            $final    = max( 0.0, $price - $discount );
            $fee_display = '$' . number_format( $final, 0 );
            if ( $discount > 0 ) {
                /* translators: 1: final fee, 2: original fee */
                $fee_display = sprintf(
                    __( '%1$s <s>%2$s</s>', 'cac-shelterluv' ),
                    '$' . number_format( $final, 0 ),
                    '$' . number_format( $price, 0 )
                );
            }
        }
        ?>
        <div class="cac-sl-animal">

            <!-- Gallery -->
            <div class="cac-sl-animal__gallery">
                <?php if ( ! empty( $photos ) ) : ?>
                    <div class="cac-sl-animal__main-wrap">
                        <img
                            id="cac-sl-main-photo"
                            class="cac-sl-animal__main-photo"
                            src="<?php echo esc_url( $photos[0] ); ?>"
                            alt="<?php echo esc_attr( $name ); ?>"
                            width="800"
                            height="600"
                            decoding="async"
                        />
                    </div>
                    <?php if ( count( $photos ) > 1 ) : ?>
                        <div class="cac-sl-animal__thumbs" role="list" aria-label="<?php esc_attr_e( 'More photos', 'cac-shelterluv' ); ?>">
                            <?php foreach ( $photos as $i => $url ) : ?>
                                <button
                                    class="cac-sl-animal__thumb <?php echo 0 === $i ? 'is-active' : ''; ?>"
                                    type="button"
                                    data-src="<?php echo esc_url( $url ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( 'Photo %d', 'cac-shelterluv' ), $i + 1 ) ); ?>"
                                    aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>"
                                    role="listitem"
                                >
                                    <img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy" decoding="async" width="120" height="90">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="cac-sl-animal__main-wrap cac-sl-animal__main-wrap--placeholder">
                        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="64" height="64" aria-hidden="true">
                            <ellipse cx="16" cy="12" rx="4" ry="5"/>
                            <ellipse cx="32" cy="12" rx="4" ry="5"/>
                            <ellipse cx="9"  cy="22" rx="3.5" ry="4.5"/>
                            <ellipse cx="39" cy="22" rx="3.5" ry="4.5"/>
                            <path d="M24 18c-8 0-14 6-11 14 1.5 3.5 5 6 11 6s9.5-2.5 11-6c3-8-3-14-11-14z"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="cac-sl-animal__details">

                <div class="cac-sl-animal__header">
                    <h2 class="cac-sl-animal__name"><?php echo esc_html( $name ); ?></h2>
                    <?php if ( $status ) : ?>
                        <span class="cac-sl-animal__status"><?php echo esc_html( $status ); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Key facts grid -->
                <dl class="cac-sl-animal__meta">
                    <?php
                    $meta = array_filter( [
                        __( 'Type',   'cac-shelterluv' ) => $type,
                        __( 'Breed',  'cac-shelterluv' ) => $breed,
                        __( 'Age',    'cac-shelterluv' ) => $age_label,
                        __( 'Sex',    'cac-shelterluv' ) => $sex ? ucfirst( $sex ) : '',
                        __( 'Color',  'cac-shelterluv' ) => $color,
                        __( 'Pattern','cac-shelterluv' ) => $pattern,
                        __( 'Size',   'cac-shelterluv' ) => $size,
                        __( 'Weight', 'cac-shelterluv' ) => $weight ? $weight . ' lbs' : '',
                        __( 'Spayed/Neutered', 'cac-shelterluv' ) => $altered ?: '',
                        __( 'Days in care',    'cac-shelterluv' ) => null !== $days_in_care
                            ? sprintf( _n( '%d day', '%d days', $days_in_care, 'cac-shelterluv' ), $days_in_care )
                            : '',
                        __( 'Adoption fee', 'cac-shelterluv' ) => $fee_display,
                    ] );
                    foreach ( $meta as $label => $value ) :
                        if ( '' === (string) $value ) continue;
                    ?>
                        <div class="cac-sl-animal__meta-item">
                            <dt><?php echo esc_html( $label ); ?></dt>
                            <dd><?php echo wp_kses( (string) $value, [ 's' => [] ] ); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <!-- Attributes -->
                <?php if ( ! empty( $pub_attributes ) ) : ?>
                    <ul class="cac-sl-animal__attributes" aria-label="<?php esc_attr_e( 'Traits', 'cac-shelterluv' ); ?>">
                        <?php foreach ( $pub_attributes as $attr ) : ?>
                            <li class="cac-sl-animal__attribute"><?php echo esc_html( $attr['AttributeName'] ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <!-- Description -->
                <?php if ( $description ) : ?>
                    <div class="cac-sl-animal__description">
                        <?php echo wp_kses_post( wpautop( $description ) ); ?>
                    </div>
                <?php endif; ?>

                <!-- Foster note -->
                <?php if ( $in_foster ) : ?>
                    <p class="cac-sl-animal__foster-note">
                        <?php esc_html_e( 'Currently in a loving foster home.', 'cac-shelterluv' ); ?>
                    </p>
                <?php endif; ?>

                <!-- CTAs -->
                <div class="cac-sl-animal__actions">
                    <?php if ( $adopt_url ) : ?>
                        <a
                            href="<?php echo esc_url( $adopt_url ); ?>"
                            class="btn btn--primary btn--lg"
                        >
                            <?php
                            echo esc_html(
                                $name
                                    /* translators: %s: pet name */
                                    ? sprintf( __( 'Adopt %s', 'cac-shelterluv' ), $name )
                                    : __( 'Start Adoption Process', 'cac-shelterluv' )
                            );
                            ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( $listing_url ) : ?>
                        <a href="<?php echo esc_url( $listing_url ); ?>" class="cac-sl-animal__back">
                            &larr; <?php esc_html_e( 'All adoptable pets', 'cac-shelterluv' ); ?>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php
    }
}

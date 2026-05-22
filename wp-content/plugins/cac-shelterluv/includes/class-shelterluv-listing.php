<?php
/**
 * Full paginated listing of adoptable pets.
 *
 * Registered as a shortcode: [cac_adoptable_animals]
 * Optional attribute: per_page (default 12, max 100).
 *
 * Pagination uses the ?animals_page=N query param so it doesn't
 * conflict with WordPress's own paged/page variables.
 *
 * Usage: add [cac_adoptable_animals] to any page's content in the editor.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Listing {

    const DEFAULT_PER_PAGE = 12;

    private CAC_ShelterLuv_API $api;

    public function __construct( CAC_ShelterLuv_API $api ) {
        $this->api = $api;
        add_shortcode( 'cac_adoptable_animals', [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets(): void {
        if ( ! $this->is_listing_page() ) {
            return;
        }

        wp_enqueue_style(
            'cac-shelterluv-listing',
            CAC_SL_URL . 'assets/css/listing.css',
            [ 'cac-main' ],
            CAC_SL_VERSION
        );
    }

    private function is_listing_page(): bool {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cac_adoptable_animals' );
    }

    public function render_shortcode( array $atts ): string {
        $atts = shortcode_atts(
            [ 'per_page' => self::DEFAULT_PER_PAGE ],
            $atts,
            'cac_adoptable_animals'
        );

        $per_page = max( 1, min( 100, (int) $atts['per_page'] ) );
        $current  = max( 1, (int) ( $_GET['animals_page'] ?? 1 ) );
        $offset   = ( $current - 1 ) * $per_page;

        $result = $this->api->get_animals_with_total( $per_page, $offset );

        ob_start();

        if ( is_wp_error( $result ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                printf(
                    '<p class="cac-sl-admin-notice">%s</p>',
                    esc_html( $result->get_error_message() )
                );
            }
            return ob_get_clean();
        }

        $animals     = $result['animals'];
        $total_count = $result['total_count'];
        $total_pages = $total_count > 0 ? (int) ceil( $total_count / $per_page ) : 1;

        // Clamp current page to valid range (e.g. if ?animals_page=99 but only 3 pages).
        if ( $current > $total_pages ) {
            $current = $total_pages;
        }

        if ( empty( $animals ) ) {
            echo '<p class="cac-sl-empty">' . esc_html__( 'No adoptable pets are listed right now — check back soon!', 'cac-shelterluv' ) . '</p>';
            return ob_get_clean();
        }

        $from = $offset + 1;
        $to   = min( $offset + count( $animals ), $total_count );
        ?>
        <div class="cac-sl-listing">

            <?php if ( $total_count > $per_page ) : ?>
                <p class="cac-sl-listing__summary">
                    <?php
                    printf(
                        /* translators: 1: first result number, 2: last result number, 3: total */
                        esc_html__( 'Showing %1$d–%2$d of %3$d adoptable pets', 'cac-shelterluv' ),
                        $from,
                        $to,
                        $total_count
                    );
                    ?>
                </p>
            <?php endif; ?>

            <div class="pets-grid">
                <?php foreach ( $animals as $animal ) : ?>
                    <?php CAC_ShelterLuv_Card::render( $animal ); ?>
                <?php endforeach; ?>
            </div>

            <?php if ( $total_pages > 1 ) : ?>
                <?php $this->render_pagination( $current, $total_pages ); ?>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    private function render_pagination( int $current, int $total_pages ): void {
        $base_url = get_permalink();
        $range    = 2; // pages shown on each side of the current page
        $start    = max( 1, $current - $range );
        $end      = min( $total_pages, $current + $range );
        ?>
        <nav
            class="cac-sl-pagination nav-links"
            aria-label="<?php esc_attr_e( 'Adoptable pets pages', 'cac-shelterluv' ); ?>"
        >
            <?php if ( $current > 1 ) : ?>
                <a
                    href="<?php echo esc_url( add_query_arg( 'animals_page', $current - 1, $base_url ) ); ?>"
                    class="page-numbers prev"
                    aria-label="<?php esc_attr_e( 'Previous page', 'cac-shelterluv' ); ?>"
                >&laquo;</a>
            <?php endif; ?>

            <?php
            if ( $start > 1 ) {
                printf(
                    '<a href="%s" class="page-numbers">1</a>',
                    esc_url( add_query_arg( 'animals_page', 1, $base_url ) )
                );
                if ( $start > 2 ) {
                    echo '<span class="page-numbers dots">&hellip;</span>';
                }
            }

            for ( $i = $start; $i <= $end; $i++ ) {
                if ( $i === $current ) {
                    printf(
                        '<span class="page-numbers current" aria-current="page">%d</span>',
                        $i
                    );
                } else {
                    printf(
                        '<a href="%s" class="page-numbers">%d</a>',
                        esc_url( add_query_arg( 'animals_page', $i, $base_url ) ),
                        $i
                    );
                }
            }

            if ( $end < $total_pages ) {
                if ( $end < $total_pages - 1 ) {
                    echo '<span class="page-numbers dots">&hellip;</span>';
                }
                printf(
                    '<a href="%s" class="page-numbers">%d</a>',
                    esc_url( add_query_arg( 'animals_page', $total_pages, $base_url ) ),
                    $total_pages
                );
            }
            ?>

            <?php if ( $current < $total_pages ) : ?>
                <a
                    href="<?php echo esc_url( add_query_arg( 'animals_page', $current + 1, $base_url ) ); ?>"
                    class="page-numbers next"
                    aria-label="<?php esc_attr_e( 'Next page', 'cac-shelterluv' ); ?>"
                >&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php
    }
}

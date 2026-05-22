<?php
/**
 * Full paginated listing of adoptable pets with filtering and sorting.
 *
 * Shortcode: [cac_adoptable_animals]
 * Attribute: per_page (default 12, max 100)
 *
 * All filtering and sorting happens in PHP against the full cached animal
 * set (fetched via get_all_animals). Pagination and filter state live in
 * URL query params so they are shareable and browser-navigable:
 *   animals_type, animals_sex, animals_age, animals_sort, animals_page
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

        wp_enqueue_script(
            'cac-shelterluv-listing',
            CAC_SL_URL . 'assets/js/listing.js',
            [],
            CAC_SL_VERSION,
            [ 'in_footer' => true, 'strategy' => 'defer' ]
        );
    }

    private function is_listing_page(): bool {
        global $post;
        return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cac_adoptable_animals' );
    }

    public function render_shortcode( array $atts ): string {
        $atts     = shortcode_atts( [ 'per_page' => self::DEFAULT_PER_PAGE ], $atts, 'cac_adoptable_animals' );
        $per_page = max( 1, min( 100, (int) $atts['per_page'] ) );

        $f_type = strtolower( sanitize_text_field( wp_unslash( $_GET['animals_type'] ?? '' ) ) );
        $f_sex  = strtolower( sanitize_text_field( wp_unslash( $_GET['animals_sex']  ?? '' ) ) );
        $f_age  = sanitize_key( $_GET['animals_age']  ?? '' );
        $sort   = sanitize_key( $_GET['animals_sort'] ?? 'name_asc' );
        $page   = max( 1, (int) ( $_GET['animals_page'] ?? 1 ) );

        $valid_sorts = [ 'name_asc', 'name_desc', 'age_asc', 'age_desc', 'stay_desc', 'stay_asc' ];
        if ( ! in_array( $sort, $valid_sorts, true ) ) {
            $sort = 'name_asc';
        }

        $valid_ages = [ 'baby', 'young', 'adult', 'senior' ];
        if ( ! in_array( $f_age, $valid_ages, true ) ) {
            $f_age = '';
        }

        $all = $this->api->get_all_animals();

        ob_start();

        if ( is_wp_error( $all ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                printf(
                    '<p class="cac-sl-admin-notice">%s</p>',
                    esc_html( $all->get_error_message() )
                );
            }
            return ob_get_clean();
        }

        // Build filter options from the unfiltered full set.
        $avail_types = $this->extract_values( $all, 'Type' );
        $avail_sexes = $this->extract_values( $all, 'Sex' );
        $avail_ages  = $this->extract_age_groups( $all );

        // Apply active filters.
        $filtered = $all;
        if ( $f_type ) {
            $filtered = array_values( array_filter( $filtered, fn( $a ) => strtolower( $a['Type'] ?? '' ) === $f_type ) );
        }
        if ( $f_sex ) {
            $filtered = array_values( array_filter( $filtered, fn( $a ) => strtolower( $a['Sex'] ?? '' ) === $f_sex ) );
        }
        if ( $f_age ) {
            $filtered = array_values( array_filter( $filtered, fn( $a ) => $this->classify_age( $a['Age'] ?? null ) === $f_age ) );
        }

        $filtered = $this->sort_animals( $filtered, $sort );

        $total       = count( $filtered );
        $total_pages = max( 1, (int) ceil( $total / $per_page ) );
        $page        = min( $page, $total_pages );
        $offset      = ( $page - 1 ) * $per_page;
        $animals     = array_slice( $filtered, $offset, $per_page );

        $has_active = $f_type || $f_sex || $f_age;

        // Params to carry through pagination links (drop defaults to keep URLs clean).
        $url_params = array_filter( [
            'animals_type' => $f_type,
            'animals_sex'  => $f_sex,
            'animals_age'  => $f_age,
            'animals_sort' => 'name_asc' !== $sort ? $sort : '',
        ] );
        ?>
        <div class="cac-sl-listing">

            <?php $this->render_filters( $avail_types, $avail_sexes, $avail_ages, $f_type, $f_sex, $f_age, $sort, $has_active ); ?>

            <?php if ( empty( $animals ) ) : ?>

                <p class="cac-sl-empty">
                    <?php if ( $has_active ) : ?>
                        <?php esc_html_e( 'No pets match your current filters — try adjusting or clearing them.', 'cac-shelterluv' ); ?>
                    <?php else : ?>
                        <?php esc_html_e( 'No adoptable pets are listed right now — check back soon!', 'cac-shelterluv' ); ?>
                    <?php endif; ?>
                </p>

            <?php else : ?>

                <?php if ( $total_pages > 1 || $has_active ) : ?>
                    <p class="cac-sl-listing__summary">
                        <?php
                        $from = $offset + 1;
                        $to   = min( $offset + count( $animals ), $total );
                        echo esc_html(
                            $has_active
                                /* translators: 1: first result, 2: last result, 3: total matching */
                                ? sprintf( __( 'Showing %1$d–%2$d of %3$d matching pets', 'cac-shelterluv' ), $from, $to, $total )
                                /* translators: 1: first result, 2: last result, 3: total */
                                : sprintf( __( 'Showing %1$d–%2$d of %3$d adoptable pets', 'cac-shelterluv' ), $from, $to, $total )
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
                    <?php $this->render_pagination( $page, $total_pages, $url_params ); ?>
                <?php endif; ?>

            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    // ── Filter bar ──────────────────────────────────────────────────────────

    private function render_filters(
        array $types,
        array $sexes,
        array $ages,
        string $f_type,
        string $f_sex,
        string $f_age,
        string $sort,
        bool $has_active
    ): void {
        $show_type = count( $types ) > 1;
        $show_sex  = count( $sexes ) > 1;
        $show_age  = count( $ages )  > 1;

        if ( ! $show_type && ! $show_sex && ! $show_age ) {
            // Only sort is available — render a minimal sort-only bar.
            $this->render_sort_only( $sort );
            return;
        }
        ?>
        <form class="cac-sl-filters" method="get" action="">

            <?php if ( $show_type ) : ?>
            <div class="cac-sl-filters__group">
                <label class="cac-sl-filters__label" for="cac-filter-type"><?php esc_html_e( 'Type', 'cac-shelterluv' ); ?></label>
                <select class="cac-sl-filters__select" id="cac-filter-type" name="animals_type" data-filter>
                    <option value=""><?php esc_html_e( 'All types', 'cac-shelterluv' ); ?></option>
                    <?php foreach ( $types as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $f_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ( $show_sex ) : ?>
            <div class="cac-sl-filters__group">
                <label class="cac-sl-filters__label" for="cac-filter-sex"><?php esc_html_e( 'Sex', 'cac-shelterluv' ); ?></label>
                <select class="cac-sl-filters__select" id="cac-filter-sex" name="animals_sex" data-filter>
                    <option value=""><?php esc_html_e( 'Any sex', 'cac-shelterluv' ); ?></option>
                    <?php foreach ( $sexes as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $f_sex, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ( $show_age ) : ?>
            <div class="cac-sl-filters__group">
                <label class="cac-sl-filters__label" for="cac-filter-age"><?php esc_html_e( 'Age', 'cac-shelterluv' ); ?></label>
                <select class="cac-sl-filters__select" id="cac-filter-age" name="animals_age" data-filter>
                    <option value=""><?php esc_html_e( 'Any age', 'cac-shelterluv' ); ?></option>
                    <?php foreach ( $ages as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $f_age, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="cac-sl-filters__group">
                <label class="cac-sl-filters__label" for="cac-filter-sort"><?php esc_html_e( 'Sort by', 'cac-shelterluv' ); ?></label>
                <select class="cac-sl-filters__select" id="cac-filter-sort" name="animals_sort">
                    <option value="name_asc"  <?php selected( $sort, 'name_asc' ); ?>><?php esc_html_e( 'Name A–Z', 'cac-shelterluv' ); ?></option>
                    <option value="name_desc" <?php selected( $sort, 'name_desc' ); ?>><?php esc_html_e( 'Name Z–A', 'cac-shelterluv' ); ?></option>
                    <option value="age_asc"   <?php selected( $sort, 'age_asc' ); ?>><?php esc_html_e( 'Youngest first', 'cac-shelterluv' ); ?></option>
                    <option value="age_desc"  <?php selected( $sort, 'age_desc' ); ?>><?php esc_html_e( 'Oldest first', 'cac-shelterluv' ); ?></option>
                    <option value="stay_desc" <?php selected( $sort, 'stay_desc' ); ?>><?php esc_html_e( 'Longest stay first', 'cac-shelterluv' ); ?></option>
                    <option value="stay_asc"  <?php selected( $sort, 'stay_asc' ); ?>><?php esc_html_e( 'Shortest stay first', 'cac-shelterluv' ); ?></option>
                </select>
            </div>

            <div class="cac-sl-filters__actions">
                <button type="submit" class="cac-sl-filters__submit btn btn--primary">
                    <?php esc_html_e( 'Apply', 'cac-shelterluv' ); ?>
                </button>
                <?php if ( $has_active ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="cac-sl-filters__clear">
                        <?php esc_html_e( 'Clear filters', 'cac-shelterluv' ); ?>
                    </a>
                <?php endif; ?>
            </div>

        </form>
        <?php
    }

    private function render_sort_only( string $sort ): void {
        ?>
        <form class="cac-sl-filters cac-sl-filters--sort-only" method="get" action="">
            <div class="cac-sl-filters__group">
                <label class="cac-sl-filters__label" for="cac-filter-sort"><?php esc_html_e( 'Sort by', 'cac-shelterluv' ); ?></label>
                <select class="cac-sl-filters__select" id="cac-filter-sort" name="animals_sort">
                    <option value="name_asc"  <?php selected( $sort, 'name_asc' ); ?>><?php esc_html_e( 'Name A–Z', 'cac-shelterluv' ); ?></option>
                    <option value="name_desc" <?php selected( $sort, 'name_desc' ); ?>><?php esc_html_e( 'Name Z–A', 'cac-shelterluv' ); ?></option>
                    <option value="age_asc"   <?php selected( $sort, 'age_asc' ); ?>><?php esc_html_e( 'Youngest first', 'cac-shelterluv' ); ?></option>
                    <option value="age_desc"  <?php selected( $sort, 'age_desc' ); ?>><?php esc_html_e( 'Oldest first', 'cac-shelterluv' ); ?></option>
                    <option value="stay_desc" <?php selected( $sort, 'stay_desc' ); ?>><?php esc_html_e( 'Longest stay first', 'cac-shelterluv' ); ?></option>
                    <option value="stay_asc"  <?php selected( $sort, 'stay_asc' ); ?>><?php esc_html_e( 'Shortest stay first', 'cac-shelterluv' ); ?></option>
                </select>
            </div>
            <div class="cac-sl-filters__actions">
                <button type="submit" class="cac-sl-filters__submit btn btn--primary">
                    <?php esc_html_e( 'Apply', 'cac-shelterluv' ); ?>
                </button>
            </div>
        </form>
        <?php
    }

    // ── Pagination ──────────────────────────────────────────────────────────

    private function render_pagination( int $current, int $total_pages, array $url_params ): void {
        $base     = get_permalink();
        $page_url = function ( int $n ) use ( $base, $url_params ): string {
            $args = $url_params;
            if ( $n > 1 ) {
                $args['animals_page'] = $n;
            }
            return add_query_arg( array_filter( $args ), $base );
        };

        $range = 2;
        $start = max( 1, $current - $range );
        $end   = min( $total_pages, $current + $range );
        ?>
        <nav
            class="cac-sl-pagination nav-links"
            aria-label="<?php esc_attr_e( 'Adoptable pets pages', 'cac-shelterluv' ); ?>"
        >
            <?php if ( $current > 1 ) : ?>
                <a href="<?php echo esc_url( $page_url( $current - 1 ) ); ?>"
                   class="page-numbers prev"
                   aria-label="<?php esc_attr_e( 'Previous page', 'cac-shelterluv' ); ?>">&laquo;</a>
            <?php endif; ?>

            <?php
            if ( $start > 1 ) {
                printf( '<a href="%s" class="page-numbers">1</a>', esc_url( $page_url( 1 ) ) );
                if ( $start > 2 ) {
                    echo '<span class="page-numbers dots">&hellip;</span>';
                }
            }

            for ( $i = $start; $i <= $end; $i++ ) {
                if ( $i === $current ) {
                    printf( '<span class="page-numbers current" aria-current="page">%d</span>', $i );
                } else {
                    printf( '<a href="%s" class="page-numbers">%d</a>', esc_url( $page_url( $i ) ), $i );
                }
            }

            if ( $end < $total_pages ) {
                if ( $end < $total_pages - 1 ) {
                    echo '<span class="page-numbers dots">&hellip;</span>';
                }
                printf( '<a href="%s" class="page-numbers">%d</a>', esc_url( $page_url( $total_pages ) ), $total_pages );
            }
            ?>

            <?php if ( $current < $total_pages ) : ?>
                <a href="<?php echo esc_url( $page_url( $current + 1 ) ); ?>"
                   class="page-numbers next"
                   aria-label="<?php esc_attr_e( 'Next page', 'cac-shelterluv' ); ?>">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php
    }

    // ── Data helpers ─────────────────────────────────────────────────────────

    /** Returns [ 'cat' => 'Cat', 'dog' => 'Dog' ] sorted by key. */
    private function extract_values( array $animals, string $field ): array {
        $values = [];
        foreach ( $animals as $animal ) {
            $raw = trim( $animal[ $field ] ?? '' );
            if ( '' === $raw ) {
                continue;
            }
            $key = strtolower( $raw );
            if ( ! isset( $values[ $key ] ) ) {
                $values[ $key ] = $raw;
            }
        }
        ksort( $values );
        return $values;
    }

    /** Returns age-group keys that are actually present in the dataset, in order. */
    private function extract_age_groups( array $animals ): array {
        $labels = [
            'baby'   => __( 'Baby (under 1 yr)', 'cac-shelterluv' ),
            'young'  => __( 'Young (1–3 yrs)', 'cac-shelterluv' ),
            'adult'  => __( 'Adult (3–8 yrs)', 'cac-shelterluv' ),
            'senior' => __( 'Senior (8+ yrs)', 'cac-shelterluv' ),
        ];
        $present = [];
        foreach ( $animals as $animal ) {
            $g = $this->classify_age( $animal['Age'] ?? null );
            if ( $g ) {
                $present[ $g ] = true;
            }
        }
        $result = [];
        foreach ( array_keys( $labels ) as $key ) {
            if ( isset( $present[ $key ] ) ) {
                $result[ $key ] = $labels[ $key ];
            }
        }
        return $result;
    }

    private function classify_age( $age ): string {
        if ( null === $age || ! is_numeric( $age ) ) {
            return '';
        }
        $m = (int) $age;
        if ( $m < 12 ) return 'baby';
        if ( $m < 36 ) return 'young';
        if ( $m < 96 ) return 'adult';
        return 'senior';
    }

    private function sort_animals( array $animals, string $sort ): array {
        usort( $animals, function ( $a, $b ) use ( $sort ) {
            // Intake time: lower value = came in earlier = longer stay.
            return match ( $sort ) {
                'name_desc' => strnatcasecmp( $b['Name'] ?? '', $a['Name'] ?? '' ),
                'age_asc'   => ( $a['Age'] ?? PHP_INT_MAX ) <=> ( $b['Age'] ?? PHP_INT_MAX ),
                'age_desc'  => ( $b['Age'] ?? -1 ) <=> ( $a['Age'] ?? -1 ),
                'stay_desc' => (int) ( $a['LastIntakeUnixTime'] ?? PHP_INT_MAX ) <=> (int) ( $b['LastIntakeUnixTime'] ?? PHP_INT_MAX ),
                'stay_asc'  => (int) ( $b['LastIntakeUnixTime'] ?? 0 ) <=> (int) ( $a['LastIntakeUnixTime'] ?? 0 ),
                default     => strnatcasecmp( $a['Name'] ?? '', $b['Name'] ?? '' ),
            };
        });
        return $animals;
    }
}

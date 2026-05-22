<?php
/**
 * Admin settings page for CAC ShelterLuv.
 *
 * Accessible at Settings > ShelterLuv in WP Admin.
 *
 * The API key is intentionally never echoed back into the form value once saved
 * — the input always renders blank so the key stays out of the HTML source.
 * A "Key saved" indicator shows whether a key is currently stored.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_Settings {

    const OPTION_KEY         = 'cac_shelterluv_api_key';
    const OPTION_API_URL     = 'cac_shelterluv_api_url';
    const OPTION_FETCH_COUNT = 'cac_shelterluv_fetch_count';
    const OPTION_DETAIL_PAGE  = 'cac_shelterluv_detail_page_id';
    const OPTION_LISTING_PAGE = 'cac_shelterluv_listing_page_id';
    const OPTION_ADOPT_URL    = 'cac_shelterluv_adopt_url';
    const MENU_SLUG       = 'cac-shelterluv';
    const NONCE_ACTION    = 'cac_sl_save_settings';
    const NONCE_FLUSH     = 'cac_sl_flush_cache';

    public function __construct() {
        add_action( 'admin_menu',    [ $this, 'register_page' ] );
        add_action( 'admin_init',    [ $this, 'handle_form' ] );
        add_action( 'admin_notices', [ $this, 'show_notices' ] );
    }

    public function register_page(): void {
        add_options_page(
            __( 'ShelterLuv Settings', 'cac-shelterluv' ),
            __( 'ShelterLuv', 'cac-shelterluv' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_page' ]
        );
    }

    public function handle_form(): void {
        if ( ! isset( $_POST['cac_sl_action'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'cac-shelterluv' ) );
        }

        $action = sanitize_key( $_POST['cac_sl_action'] );

        if ( 'save_key' === $action ) {
            check_admin_referer( self::NONCE_ACTION );

            $raw_key = isset( $_POST['cac_sl_api_key'] ) ? trim( $_POST['cac_sl_api_key'] ) : '';
            if ( '' !== $raw_key ) {
                $clean = preg_replace( '/[^\x20-\x7E]/', '', $raw_key );
                update_option( self::OPTION_KEY, $clean, false );
            }

            $raw_url = isset( $_POST['cac_sl_api_url'] ) ? trim( $_POST['cac_sl_api_url'] ) : '';
            if ( '' !== $raw_url ) {
                update_option( self::OPTION_API_URL, esc_url_raw( $raw_url ), false );
            } else {
                delete_option( self::OPTION_API_URL );
            }

            $raw_count = isset( $_POST['cac_sl_fetch_count'] ) ? (int) $_POST['cac_sl_fetch_count'] : 0;
            if ( $raw_count >= 1 && $raw_count <= 100 ) {
                update_option( self::OPTION_FETCH_COUNT, $raw_count, false );
            } elseif ( 0 === $raw_count ) {
                delete_option( self::OPTION_FETCH_COUNT );
            }

            $raw_page_id = isset( $_POST['cac_sl_detail_page_id'] ) ? (int) $_POST['cac_sl_detail_page_id'] : 0;
            if ( $raw_page_id > 0 ) {
                update_option( self::OPTION_DETAIL_PAGE, $raw_page_id, false );
            } else {
                delete_option( self::OPTION_DETAIL_PAGE );
            }

            $raw_listing_page_id = isset( $_POST['cac_sl_listing_page_id'] ) ? (int) $_POST['cac_sl_listing_page_id'] : 0;
            if ( $raw_listing_page_id > 0 ) {
                update_option( self::OPTION_LISTING_PAGE, $raw_listing_page_id, false );
            } else {
                delete_option( self::OPTION_LISTING_PAGE );
            }

            $raw_adopt_url = isset( $_POST['cac_sl_adopt_url'] ) ? trim( $_POST['cac_sl_adopt_url'] ) : '';
            if ( '' !== $raw_adopt_url ) {
                update_option( self::OPTION_ADOPT_URL, esc_url_raw( $raw_adopt_url ), false );
            } else {
                delete_option( self::OPTION_ADOPT_URL );
            }

            set_transient( 'cac_sl_admin_notice', 'saved', 30 );

            wp_safe_redirect( add_query_arg( 'page', self::MENU_SLUG, admin_url( 'options-general.php' ) ) );
            exit;
        }

        if ( 'clear_key' === $action ) {
            check_admin_referer( self::NONCE_ACTION );
            delete_option( self::OPTION_KEY );
            set_transient( 'cac_sl_admin_notice', 'cleared', 30 );

            wp_safe_redirect( add_query_arg( 'page', self::MENU_SLUG, admin_url( 'options-general.php' ) ) );
            exit;
        }

        if ( 'flush_cache' === $action ) {
            check_admin_referer( self::NONCE_FLUSH );
            $api = new CAC_ShelterLuv_API();
            $api->flush_cache();
            set_transient( 'cac_sl_admin_notice', 'flushed', 30 );

            wp_safe_redirect( add_query_arg( 'page', self::MENU_SLUG, admin_url( 'options-general.php' ) ) );
            exit;
        }
    }

    public function show_notices(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'settings_page_' . self::MENU_SLUG !== $screen->id ) {
            return;
        }

        $notice = get_transient( 'cac_sl_admin_notice' );
        if ( ! $notice ) {
            return;
        }
        delete_transient( 'cac_sl_admin_notice' );

        $messages = [
            'saved'   => __( 'API key saved.', 'cac-shelterluv' ),
            'cleared' => __( 'API key removed.', 'cac-shelterluv' ),
            'flushed' => __( 'Animal cache cleared. Fresh data will be fetched on next page load.', 'cac-shelterluv' ),
        ];

        if ( isset( $messages[ $notice ] ) ) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html( $messages[ $notice ] )
            );
        }
    }

    public function render_page(): void {
        $key_in_constant      = defined( 'CAC_SHELTERLUV_API_KEY' ) && CAC_SHELTERLUV_API_KEY;
        $key_in_db            = '' !== (string) get_option( self::OPTION_KEY, '' );
        $key_active      = $key_in_constant || $key_in_db;
        $url_in_constant = defined( 'CAC_SHELTERLUV_API_URL' ) && CAC_SHELTERLUV_API_URL;
        $url_saved       = (string) get_option( self::OPTION_API_URL, '' );
        $url_display     = $url_in_constant
            ? (string) CAC_SHELTERLUV_API_URL
            : $url_saved;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'ShelterLuv Settings', 'cac-shelterluv' ); ?></h1>

            <h2><?php esc_html_e( 'API Key', 'cac-shelterluv' ); ?></h2>

            <?php if ( $key_in_constant ) : ?>
                <div class="notice notice-info inline">
                    <p>
                        <?php esc_html_e( 'The API key is defined as the CAC_SHELTERLUV_API_KEY constant in wp-config.php and cannot be changed here.', 'cac-shelterluv' ); ?>
                    </p>
                </div>
            <?php else : ?>
                <p>
                    <?php
                    echo wp_kses(
                        sprintf(
                            /* translators: %s: constant name */
                            __( 'Enter your ShelterLuv API key below. For better security on production servers, define it as a PHP constant instead: <code>define( \'CAC_SHELTERLUV_API_KEY\', \'your-key\' );</code> in <code>wp-config.php</code>.', 'cac-shelterluv' ),
                            'CAC_SHELTERLUV_API_KEY'
                        ),
                        [ 'code' => [] ]
                    );
                    ?>
                </p>

                <?php if ( $key_in_db ) : ?>
                    <p>
                        <strong><?php esc_html_e( 'Status:', 'cac-shelterluv' ); ?></strong>
                        <span style="color:#3a7d44;">&#10003; <?php esc_html_e( 'API key is saved.', 'cac-shelterluv' ); ?></span>
                    </p>
                <?php else : ?>
                    <p>
                        <strong><?php esc_html_e( 'Status:', 'cac-shelterluv' ); ?></strong>
                        <span style="color:#d63638;">&#10007; <?php esc_html_e( 'No API key saved. The homepage carousel will not be displayed.', 'cac-shelterluv' ); ?></span>
                    </p>
                <?php endif; ?>

                <form method="post" action="">
                    <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                    <input type="hidden" name="cac_sl_action" value="save_key">

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_api_key"><?php esc_html_e( 'API Key', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <input
                                    type="password"
                                    id="cac_sl_api_key"
                                    name="cac_sl_api_key"
                                    class="regular-text"
                                    value=""
                                    autocomplete="off"
                                    placeholder="<?php echo $key_in_db ? esc_attr__( 'Enter a new key to replace the saved one', 'cac-shelterluv' ) : ''; ?>"
                                />
                                <p class="description">
                                    <?php esc_html_e( 'The key is never displayed after saving. Leave blank to keep the current key.', 'cac-shelterluv' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button( __( 'Save Settings', 'cac-shelterluv' ) ); ?>
                </form>

                <?php if ( $key_in_db ) : ?>
                    <form method="post" action="" style="margin-top: 0.5rem;">
                        <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                        <input type="hidden" name="cac_sl_action" value="clear_key">
                        <?php submit_button( __( 'Remove Saved Key', 'cac-shelterluv' ), 'delete', 'submit', false ); ?>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ( $key_active ) : ?>
                <hr>
                <h2><?php esc_html_e( 'Carousel Settings', 'cac-shelterluv' ); ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                    <input type="hidden" name="cac_sl_action" value="save_key">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_api_url"><?php esc_html_e( 'API Base URL', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <?php if ( $url_in_constant ) : ?>
                                    <input
                                        type="url"
                                        class="large-text"
                                        value="<?php echo esc_attr( $url_display ); ?>"
                                        disabled
                                    />
                                    <p class="description">
                                        <?php esc_html_e( 'Set via the CAC_SHELTERLUV_API_URL constant in wp-config.php.', 'cac-shelterluv' ); ?>
                                    </p>
                                <?php else : ?>
                                    <input
                                        type="url"
                                        id="cac_sl_api_url"
                                        name="cac_sl_api_url"
                                        class="large-text"
                                        value="<?php echo esc_attr( $url_display ); ?>"
                                        placeholder="https://www.shelterluv.com/api/v1"
                                    />
                                    <p class="description">
                                        <?php esc_html_e( 'The ShelterLuv API base URL (without a trailing slash). The plugin appends /animals to fetch adoptable pets.', 'cac-shelterluv' ); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_fetch_count"><?php esc_html_e( 'Animals to Show', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <input
                                    type="number"
                                    id="cac_sl_fetch_count"
                                    name="cac_sl_fetch_count"
                                    class="small-text"
                                    value="<?php echo esc_attr( (int) get_option( self::OPTION_FETCH_COUNT, CAC_ShelterLuv_Carousel::DEFAULT_FETCH_COUNT ) ); ?>"
                                    min="1"
                                    max="100"
                                />
                                <p class="description">
                                    <?php esc_html_e( 'Number of adoptable animals to load into the carousel (1–100). Default: 8.', 'cac-shelterluv' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_detail_page_id"><?php esc_html_e( 'Animal Detail Page', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <?php
                                wp_dropdown_pages( [
                                    'selected'          => get_option( self::OPTION_DETAIL_PAGE, 0 ),
                                    'name'              => 'cac_sl_detail_page_id',
                                    'id'                => 'cac_sl_detail_page_id',
                                    'show_option_none'  => __( '— Select a page —', 'cac-shelterluv' ),
                                    'option_none_value' => '0',
                                ] );
                                ?>
                                <p class="description">
                                    <?php esc_html_e( 'Page containing the [cac_animal_detail] shortcode. Animal cards will link here instead of going nowhere.', 'cac-shelterluv' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_listing_page_id"><?php esc_html_e( 'Adoptable Animals Page', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <?php
                                wp_dropdown_pages( [
                                    'selected'          => get_option( self::OPTION_LISTING_PAGE, 0 ),
                                    'name'              => 'cac_sl_listing_page_id',
                                    'id'                => 'cac_sl_listing_page_id',
                                    'show_option_none'  => __( '— Select a page —', 'cac-shelterluv' ),
                                    'option_none_value' => '0',
                                ] );
                                ?>
                                <p class="description">
                                    <?php esc_html_e( 'Page containing the [cac_adoptable_animals] shortcode. Used for the "← All adoptable pets" back link on animal detail pages.', 'cac-shelterluv' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cac_sl_adopt_url"><?php esc_html_e( 'Adoption Contact URL', 'cac-shelterluv' ); ?></label>
                            </th>
                            <td>
                                <input
                                    type="url"
                                    id="cac_sl_adopt_url"
                                    name="cac_sl_adopt_url"
                                    class="large-text"
                                    value="<?php echo esc_attr( get_option( self::OPTION_ADOPT_URL, '' ) ); ?>"
                                    placeholder="https://example.com/adopt"
                                />
                                <p class="description">
                                    <?php esc_html_e( 'URL shown as the "Adopt Me" button on animal detail pages. Leave blank to hide the button.', 'cac-shelterluv' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Save Settings', 'cac-shelterluv' ) ); ?>
                </form>
                <hr>
                <h2><?php esc_html_e( 'Cache', 'cac-shelterluv' ); ?></h2>
                <p><?php esc_html_e( 'Animal data is cached for 30 minutes to match ShelterLuv\'s own refresh window. Clear the cache to force a fresh API request immediately.', 'cac-shelterluv' ); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field( self::NONCE_FLUSH ); ?>
                    <input type="hidden" name="cac_sl_action" value="flush_cache">
                    <?php submit_button( __( 'Clear Animal Cache', 'cac-shelterluv' ), 'secondary' ); ?>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }
}

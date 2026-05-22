<?php
/**
 * ShelterLuv API client.
 *
 * Responsible solely for HTTP requests to the ShelterLuv API.
 * No display or WordPress hooks here — just data.
 */

defined( 'ABSPATH' ) || exit;

class CAC_ShelterLuv_API {

    /** Transient TTL matches ShelterLuv's server-side cache refresh window. */
    const CACHE_TTL = 30 * MINUTE_IN_SECONDS;

    private string $api_key;
    private string $api_url;

    public function __construct() {
        $this->api_key = $this->resolve_api_key();
        $this->api_url = $this->resolve_api_url();
    }

    /**
     * Constant in wp-config.php takes priority over the DB option so the key
     * never needs to be stored in the database on production.
     */
    private function resolve_api_key(): string {
        if ( defined( 'CAC_SHELTERLUV_API_KEY' ) && CAC_SHELTERLUV_API_KEY ) {
            return (string) CAC_SHELTERLUV_API_KEY;
        }
        return (string) get_option( 'cac_shelterluv_api_key', '' );
    }

    /** CAC_SHELTERLUV_API_URL constant → DB option → empty (no built-in default). */
    private function resolve_api_url(): string {
        if ( defined( 'CAC_SHELTERLUV_API_URL' ) && CAC_SHELTERLUV_API_URL ) {
            return esc_url_raw( rtrim( (string) CAC_SHELTERLUV_API_URL, '/' ) );
        }
        $saved = (string) get_option( 'cac_shelterluv_api_url', '' );
        return '' !== $saved ? rtrim( $saved, '/' ) : '';
    }

    public function has_api_key(): bool {
        return '' !== $this->api_key;
    }

    public function has_api_url(): bool {
        return '' !== $this->api_url;
    }

    public function get_api_url(): string {
        return $this->api_url;
    }

    /** Returns true when the key comes from a wp-config.php constant. */
    public function key_is_constant(): bool {
        return defined( 'CAC_SHELTERLUV_API_KEY' ) && CAC_SHELTERLUV_API_KEY;
    }

    /** Returns true when the API URL comes from a wp-config.php constant. */
    public function url_is_constant(): bool {
        return defined( 'CAC_SHELTERLUV_API_URL' ) && CAC_SHELTERLUV_API_URL;
    }

    /**
     * Fetch publishable (adoptable) animals from ShelterLuv.
     *
     * Results are cached in a transient keyed by limit + offset so multiple
     * callers with different pagination don't collide.
     *
     * @param int $limit  1–100
     * @param int $offset Pagination offset
     * @return array[]|WP_Error  Array of animal objects on success.
     */
    public function get_animals( int $limit = 8, int $offset = 0 ) {
        $result = $this->fetch_animals( $limit, $offset );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        return $result['animals'];
    }

    /**
     * Like get_animals() but also returns the total count for pagination.
     *
     * @param int $limit  1–100
     * @param int $offset Pagination offset
     * @return array{animals: array[], total_count: int}|WP_Error
     */
    public function get_animals_with_total( int $limit = 8, int $offset = 0 ) {
        return $this->fetch_animals( $limit, $offset );
    }

    /**
     * Internal: hit the API (or cache) and return animals + total_count.
     *
     * @return array{animals: array[], total_count: int}|WP_Error
     */
    private function fetch_animals( int $limit, int $offset ) {
        $limit  = max( 1, min( 100, $limit ) );
        $offset = max( 0, $offset );

        $url_hash  = substr( md5( $this->api_url ), 0, 8 );
        $cache_key = "cac_sl_animals_{$url_hash}_{$limit}_{$offset}";
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            // New cache format stores ['animals' => [], 'total_count' => N].
            // Legacy entries are a plain animals array — convert on the fly.
            if ( is_array( $cached ) && array_key_exists( 'animals', $cached ) ) {
                return $cached;
            }
            return [ 'animals' => is_array( $cached ) ? $cached : [], 'total_count' => 0 ];
        }

        $response = wp_remote_get(
            add_query_arg(
                [
                    'status_type' => 'publishable',
                    'limit'       => $limit,
                    'offset'      => $offset,
                ],
                $this->api_url . '/animals'
            ),
            [
                'headers' => [ 'x-api-key' => $this->api_key ],
                'timeout' => 10,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== (int) $code ) {
            return new WP_Error(
                'api_http_error',
                /* translators: %d: HTTP status code */
                sprintf( __( 'ShelterLuv API returned status %d.', 'cac-shelterluv' ), $code )
            );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) ) {
            return new WP_Error( 'api_failure', __( 'ShelterLuv API reported an unsuccessful response.', 'cac-shelterluv' ) );
        }

        $animals     = $body['animals'] ?? [];
        $total_count = isset( $body['total_count'] ) ? (int) $body['total_count'] : count( $animals );

        $result = [ 'animals' => $animals, 'total_count' => $total_count ];
        set_transient( $cache_key, $result, self::CACHE_TTL );

        return $result;
    }

    /**
     * Fetch the complete set of publishable animals, paging through the API
     * in batches of 100 until exhausted. The full list is cached as a single
     * transient so filtering and sorting can happen entirely in PHP.
     *
     * @return array[]|WP_Error
     */
    public function get_all_animals() {
        $url_hash  = substr( md5( $this->api_url ), 0, 8 );
        $cache_key = "cac_sl_all_{$url_hash}";
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $all    = [];
        $batch  = 100;
        $offset = 0;
        $cap    = 500; // safety limit

        while ( count( $all ) < $cap ) {
            $result = $this->fetch_animals( $batch, $offset );
            if ( is_wp_error( $result ) ) {
                return $result;
            }
            $chunk = $result['animals'];
            $all   = array_merge( $all, $chunk );
            if ( count( $chunk ) < $batch ) {
                break;
            }
            $offset += $batch;
        }

        set_transient( $cache_key, $all, self::CACHE_TTL );
        return $all;
    }

    /**
     * Bust all cached animal transients.
     * Called from the settings page "Clear Cache" button.
     */
    public function flush_cache(): void {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cac_sl_%' OR option_name LIKE '_transient_timeout_cac_sl_%'"
        );
    }
}

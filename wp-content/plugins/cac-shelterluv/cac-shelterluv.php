<?php
/**
 * Plugin Name: CAC ShelterLuv
 * Plugin URI:  https://github.com/Lanham-Software-James/companion-animal-coalition
 * Description: Pulls live adoptable animals from the ShelterLuv API. Renders a homepage carousel via the cac_adoptable_pets_grid action hook and a full paginated listing via the [cac_adoptable_animals] shortcode.
 * Version:     1.0.0
 * Author:      Companion Animal Coalition
 * Text Domain: cac-shelterluv
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'CAC_SL_VERSION', '1.0.0' );
define( 'CAC_SL_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAC_SL_URL', plugin_dir_url( __FILE__ ) );

require_once CAC_SL_DIR . 'includes/class-shelterluv-api.php';
require_once CAC_SL_DIR . 'includes/class-shelterluv-card.php';
require_once CAC_SL_DIR . 'includes/class-shelterluv-carousel.php';
require_once CAC_SL_DIR . 'includes/class-shelterluv-listing.php';
require_once CAC_SL_DIR . 'includes/class-shelterluv-animal-detail.php';

if ( is_admin() ) {
    require_once CAC_SL_DIR . 'admin/class-shelterluv-settings.php';
    new CAC_ShelterLuv_Settings();
}

$_cac_sl_api = new CAC_ShelterLuv_API();
if ( $_cac_sl_api->has_api_key() && $_cac_sl_api->has_api_url() ) {
    new CAC_ShelterLuv_Carousel( $_cac_sl_api );
    new CAC_ShelterLuv_Listing( $_cac_sl_api );
    new CAC_ShelterLuv_Animal_Detail( $_cac_sl_api );
}
unset( $_cac_sl_api );

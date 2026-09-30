<?php
/** WooCommerce integration; retain native templates and extension hooks. */
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
    add_theme_support( 'woocommerce', [
        'product_grid' => [ 'default_columns' => 3, 'default_rows' => 4 ],
    ] );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
} );

add_action( 'wp', function () {
    if ( ! function_exists( 'is_woocommerce' ) || ! is_woocommerce() ) {
        return;
    }

    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
    remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

    add_action( 'woocommerce_before_main_content', function () {
        echo '<main id="main-content" class="site-main cac-shop"><div class="container">';
    }, 10 );
    add_action( 'woocommerce_after_main_content', function () {
        echo '</div></main>';
    }, 10 );
} );

add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
        wp_enqueue_style(
            'cac-shop',
            get_template_directory_uri() . '/assets/css/shop.css',
            [ 'cac-main', 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen' ],
            (string) filemtime( get_template_directory() . '/assets/css/shop.css' )
        );
    }
}, 20 );

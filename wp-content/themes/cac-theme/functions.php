<?php
defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────
// Theme Setup
// ──────────────────────────────────────────
add_action( 'after_setup_theme', 'cac_setup' );
function cac_setup() {
    load_theme_textdomain( 'cac-theme', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ] );
    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => [ 'site-title', 'site-description' ],
    ] );
    add_theme_support( 'customize-selective-refresh-widgets' );

    register_nav_menus( [
        'primary'  => __( 'Primary Navigation', 'cac-theme' ),
        'footer-1' => __( 'Footer: About', 'cac-theme' ),
        'footer-2' => __( 'Footer: Get Involved', 'cac-theme' ),
        'footer-3' => __( 'Footer: Resources', 'cac-theme' ),
    ] );
}

// ──────────────────────────────────────────
// Enqueue Assets
// ──────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'cac_enqueue_assets' );
function cac_enqueue_assets() {
    wp_enqueue_style(
        'cac-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap',
        [],
        null
    );

    // style.css is minimal (theme header only); main.css has all styles
    wp_enqueue_style(
        'cac-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [ 'cac-fonts' ],
        '1.0.0'
    );

    wp_enqueue_script(
        'cac-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.0',
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );

    // Pass theme URI to JS for any dynamic asset references
    wp_localize_script( 'cac-main', 'cacTheme', [
        'themeUri' => get_template_directory_uri(),
    ] );
}

// ──────────────────────────────────────────
// Customizer
// ──────────────────────────────────────────
add_action( 'customize_register', 'cac_customize_register' );
function cac_customize_register( WP_Customize_Manager $wp_customize ) {

    // ── Hero Section ──────────────────────
    $wp_customize->add_section( 'cac_hero', [
        'title'    => __( 'Hero Section', 'cac-theme' ),
        'priority' => 30,
    ] );

    $wp_customize->add_setting( 'cac_hero_image', [
        'default'           => '',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'cac_hero_image', [
        'label'     => __( 'Hero Background Image', 'cac-theme' ),
        'section'   => 'cac_hero',
        'mime_type' => 'image',
    ] ) );

    $wp_customize->add_setting( 'cac_hero_cta_adopt_url', [
        'default'           => '/adopt',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'cac_hero_cta_adopt_url', [
        'label'   => __( 'Adopt CTA URL', 'cac-theme' ),
        'section' => 'cac_hero',
        'type'    => 'url',
    ] );

    $wp_customize->add_setting( 'cac_hero_cta_donate_url', [
        'default'           => '/donate',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'cac_hero_cta_donate_url', [
        'label'   => __( 'Donate CTA URL', 'cac-theme' ),
        'section' => 'cac_hero',
        'type'    => 'url',
    ] );

    // ── Impact Stats ──────────────────────
    $wp_customize->add_section( 'cac_stats', [
        'title'    => __( 'Impact Statistics', 'cac-theme' ),
        'priority' => 31,
    ] );

    $stats_defaults = [
        [ 'number' => '6,800+', 'label' => 'Animals Rescued Since 2023' ],
        [ 'number' => '2,500+', 'label' => 'Animals Adopted' ],
        [ 'number' => '18,000+', 'label' => 'Animals Provided Medical Care' ],
        [ 'number' => '10,000+', 'label' => 'Community Supporters & Volunteers' ],
    ];

    for ( $i = 1; $i <= 4; $i++ ) {
        $wp_customize->add_setting( "cac_stat_{$i}_number", [
            'default'           => $stats_defaults[ $i - 1 ]['number'],
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        $wp_customize->add_control( "cac_stat_{$i}_number", [
            'label'   => sprintf( __( 'Stat %d: Number', 'cac-theme' ), $i ),
            'section' => 'cac_stats',
        ] );

        $wp_customize->add_setting( "cac_stat_{$i}_label", [
            'default'           => $stats_defaults[ $i - 1 ]['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ] );
        $wp_customize->add_control( "cac_stat_{$i}_label", [
            'label'   => sprintf( __( 'Stat %d: Label', 'cac-theme' ), $i ),
            'section' => 'cac_stats',
        ] );
    }
}

// ──────────────────────────────────────────
// Template Helpers
// ──────────────────────────────────────────

/**
 * Returns the hero background image URL from the customizer,
 * falling back to a theme placeholder if none is set.
 */
function cac_get_hero_image_url(): string {
    $attachment_id = get_theme_mod( 'cac_hero_image', 0 );
    if ( $attachment_id ) {
        $src = wp_get_attachment_image_url( $attachment_id, 'full' );
        if ( $src ) {
            return $src;
        }
    }
    return get_template_directory_uri() . '/assets/images/hero-placeholder.jpg';
}

/**
 * Returns an array of stat data for the impact section.
 *
 * @return array{ number: string, label: string, icon: string }[]
 */
function cac_get_impact_stats(): array {
    $icons = [
        '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
        '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
        '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ];

    $stats = [];
    for ( $i = 1; $i <= 4; $i++ ) {
        $defaults = [
            1 => [ 'number' => '6,800+',  'label' => 'Animals Rescued Since 2023' ],
            2 => [ 'number' => '2,500+',  'label' => 'Animals Adopted' ],
            3 => [ 'number' => '18,000+', 'label' => 'Animals Provided Medical Care' ],
            4 => [ 'number' => '10,000+', 'label' => 'Community Supporters &amp; Volunteers' ],
        ];
        $stats[] = [
            'number' => esc_html( get_theme_mod( "cac_stat_{$i}_number", $defaults[ $i ]['number'] ) ),
            'label'  => esc_html( get_theme_mod( "cac_stat_{$i}_label",  $defaults[ $i ]['label'] ) ),
            'icon'   => $icons[ $i - 1 ],
        ];
    }
    return $stats;
}

// ──────────────────────────────────────────
// Body Classes
// ──────────────────────────────────────────
add_filter( 'body_class', 'cac_body_classes' );
function cac_body_classes( array $classes ): array {
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }
    return $classes;
}

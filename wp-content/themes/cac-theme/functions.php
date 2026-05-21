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

    // ── Page CTA Banner ───────────────────
    $wp_customize->add_section( 'cac_page_cta', [
        'title'    => __( 'Page CTA Banner', 'cac-theme' ),
        'priority' => 32,
    ] );

    $wp_customize->add_setting( 'cac_page_cta_heading', [
        'default'           => __( 'Ready to Make a Difference?', 'cac-theme' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'cac_page_cta_heading', [
        'label'   => __( 'CTA Heading', 'cac-theme' ),
        'section' => 'cac_page_cta',
    ] );

    $wp_customize->add_setting( 'cac_page_cta_text', [
        'default'           => __( 'There are so many ways you can help animals in need. Together, we can build a better future for every pet.', 'cac-theme' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'cac_page_cta_text', [
        'label'   => __( 'CTA Body Text', 'cac-theme' ),
        'section' => 'cac_page_cta',
        'type'    => 'textarea',
    ] );

    $wp_customize->add_setting( 'cac_page_cta_btn_label', [
        'default'           => __( 'Get Involved', 'cac-theme' ),
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'cac_page_cta_btn_label', [
        'label'   => __( 'CTA Button Label', 'cac-theme' ),
        'section' => 'cac_page_cta',
    ] );

    $wp_customize->add_setting( 'cac_page_cta_btn_url', [
        'default'           => '/get-involved',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'cac_page_cta_btn_url', [
        'label'   => __( 'CTA Button URL', 'cac-theme' ),
        'section' => 'cac_page_cta',
        'type'    => 'url',
    ] );
}

// ──────────────────────────────────────────
// Block Editor Support
// ──────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
    add_editor_style( 'assets/css/main.css' );
} );

// ──────────────────────────────────────────
// Block Patterns
// ──────────────────────────────────────────
add_action( 'init', 'cac_register_block_patterns' );
function cac_register_block_patterns() {
    register_block_pattern_category( 'cac', [
        'label' => __( 'CAC Theme', 'cac-theme' ),
    ] );

    // ── Content Block: Image Right ──────
    register_block_pattern( 'cac/content-block-image-right', [
        'title'       => __( 'Content Block – Image Right', 'cac-theme' ),
        'description' => __( 'Two-column section: text on the left, image on the right. Cream background.', 'cac-theme' ),
        'categories'  => [ 'cac' ],
        'content'     => <<<'EOT'
<!-- wp:group {"className":"content-block content-block--cream","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group content-block content-block--cream">

<!-- wp:columns {"isStackedOnMobile":true,"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">

<!-- wp:paragraph {"className":"content-block__label"} -->
<p class="content-block__label">Section Heading</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">A Meaningful Title Goes Here</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use this space to share more information about this topic. Keep it clear, compassionate, and focused on the mission.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>You can expand on the details, tell a story, or provide helpful context that supports the purpose of this page.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">Primary Button</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:paragraph -->
<p><a href="#">Secondary Link →</a></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","className":"content-block__image-col"} -->
<div class="wp-block-column content-block__image-col" style="flex-basis:50%">

<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="" alt="" /></figure>
<!-- /wp:image -->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
EOT,
    ] );

    // ── Content Block: Image Left ───────
    register_block_pattern( 'cac/content-block-image-left', [
        'title'       => __( 'Content Block – Image Left', 'cac-theme' ),
        'description' => __( 'Two-column section: image on the left, text with bullet list on the right. White background.', 'cac-theme' ),
        'categories'  => [ 'cac' ],
        'content'     => <<<'EOT'
<!-- wp:group {"className":"content-block","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group content-block">

<!-- wp:columns {"isStackedOnMobile":true,"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"width":"50%","className":"content-block__image-col"} -->
<div class="wp-block-column content-block__image-col" style="flex-basis:50%">

<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="" alt="" /></figure>
<!-- /wp:image -->

</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">

<!-- wp:paragraph {"className":"content-block__label"} -->
<p class="content-block__label">Another Section</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Another Title Can Go Here</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use this area to add more information, highlight key points, or break up content with visuals that support your message.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><li>Key point or benefit goes here</li><li>Key point or benefit goes here</li><li>Key point or benefit goes here</li></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#">Learn More →</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
EOT,
    ] );

    // ── Values Strip ─────────────────────
    register_block_pattern( 'cac/values-strip', [
        'title'       => __( 'Values Strip', 'cac-theme' ),
        'description' => __( 'Four-column strip showing organizational values with icons (2×2 on mobile, 4-column on desktop).', 'cac-theme' ),
        'categories'  => [ 'cac' ],
        'content'     => <<<'EOT'
<!-- wp:group {"className":"values-strip","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group values-strip">

<!-- wp:columns {"isStackedOnMobile":false} -->
<div class="wp-block-columns">

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:html -->
<div class="values-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="4" r="1.5"/><circle cx="17" cy="4" r="1.5"/><circle cx="4" cy="10" r="1.5"/><circle cx="20" cy="10" r="1.5"/><path d="M12 21c-3.87 0-7-2.69-7-6 0-3 2.5-4 4-4h6c1.5 0 4 1 4 4 0 3.31-3.13 6-7 6z"/></svg></div>
<!-- /wp:html -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Compassion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We lead with kindness and put animals first in everything we do.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:html -->
<div class="values-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg></div>
<!-- /wp:html -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Integrity</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We are transparent, accountable, and committed to doing what's right.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:html -->
<div class="values-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
<!-- /wp:html -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Community</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We believe in the power of working together to create lasting change.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:html -->
<div class="values-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
<!-- /wp:html -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Respect</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We treat every animal, person, and partner with dignity and respect.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
EOT,
    ] );
}

// ──────────────────────────────────────────
// Template Helpers
// ──────────────────────────────────────────

/**
 * Outputs an accessible breadcrumb trail for internal pages.
 */
function cac_breadcrumb(): void {
    if ( is_front_page() ) return;

    $items   = [];
    $items[] = sprintf(
        '<li class="breadcrumb__item"><a href="%s">%s</a></li>',
        esc_url( home_url( '/' ) ),
        esc_html__( 'Home', 'cac-theme' )
    );

    if ( is_page() ) {
        $ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
        foreach ( $ancestors as $ancestor_id ) {
            $items[] = sprintf(
                '<li class="breadcrumb__item"><a href="%s">%s</a></li>',
                esc_url( get_permalink( $ancestor_id ) ),
                esc_html( get_the_title( $ancestor_id ) )
            );
        }
        $items[] = sprintf(
            '<li class="breadcrumb__item breadcrumb__item--current" aria-current="page">%s</li>',
            esc_html( get_the_title() )
        );
    } elseif ( is_single() ) {
        $category = get_the_category();
        if ( $category ) {
            $items[] = sprintf(
                '<li class="breadcrumb__item"><a href="%s">%s</a></li>',
                esc_url( get_category_link( $category[0]->term_id ) ),
                esc_html( $category[0]->name )
            );
        }
        $items[] = sprintf(
            '<li class="breadcrumb__item breadcrumb__item--current" aria-current="page">%s</li>',
            esc_html( get_the_title() )
        );
    }

    printf(
        '<nav class="breadcrumb" aria-label="%s"><ol class="breadcrumb__list">%s</ol></nav>',
        esc_attr__( 'Breadcrumb', 'cac-theme' ),
        implode( '', $items )
    );
}

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

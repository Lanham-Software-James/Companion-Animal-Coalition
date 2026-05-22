<?php
$hero_image_url = cac_get_hero_image_url();
$adopt_url      = esc_url( get_theme_mod( 'cac_hero_cta_adopt_url', '/adopt' ) );
$donate_url     = esc_url( get_theme_mod( 'cac_hero_cta_donate_url', '/donate' ) );
$line1          = get_theme_mod( 'cac_hero_heading_line1',   __( 'Rescue.',       'cac-theme' ) );
$line2          = get_theme_mod( 'cac_hero_heading_line2',   __( 'Rehabilitate.', 'cac-theme' ) );
$line3          = get_theme_mod( 'cac_hero_heading_line3',   __( 'Rehome.',       'cac-theme' ) );
$accent         = get_theme_mod( 'cac_hero_heading_accent',  __( 'Repeat.',       'cac-theme' ) );
$subtitle       = get_theme_mod( 'cac_hero_subtitle',        __( "We're building a community where every companion animal is valued, protected, and given the chance to thrive.", 'cac-theme' ) );
$adopt_label    = get_theme_mod( 'cac_hero_cta_adopt_label', __( 'Adopt a Pet',   'cac-theme' ) );
$donate_label   = get_theme_mod( 'cac_hero_cta_donate_label', __( 'Donate Today', 'cac-theme' ) );
?>

<section class="hero" aria-label="<?php esc_attr_e( 'Welcome to Companion Animal Coalition', 'cac-theme' ); ?>">

    <!-- Mobile: image renders first via CSS order -->
    <div class="hero__image" role="img" aria-label="<?php esc_attr_e( 'A golden retriever and tabby cat resting together', 'cac-theme' ); ?>"
         style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');">
    </div>

    <div class="hero__content">
        <h1 class="hero__title">
            <?php echo esc_html( $line1 ); ?><br>
            <?php echo esc_html( $line2 ); ?><br>
            <?php echo esc_html( $line3 ); ?><br>
            <span class="hero__title-accent"><?php echo esc_html( $accent ); ?></span>
        </h1>

        <p class="hero__subtitle">
            <?php echo esc_html( $subtitle ); ?>
        </p>

        <div class="hero__actions">
            <a href="<?php echo $adopt_url; ?>" class="btn btn--primary btn--lg">
                <?php echo esc_html( $adopt_label ); ?>
            </a>
            <a href="<?php echo $donate_url; ?>" class="btn btn--outline btn--lg">
                <?php echo esc_html( $donate_label ); ?>
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </a>
        </div>
    </div>

</section>

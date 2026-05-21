<?php
$hero_image_url  = cac_get_hero_image_url();
$adopt_url       = esc_url( get_theme_mod( 'cac_hero_cta_adopt_url', '/adopt' ) );
$donate_url      = esc_url( get_theme_mod( 'cac_hero_cta_donate_url', '/donate' ) );
?>

<section class="hero" aria-label="<?php esc_attr_e( 'Welcome to Companion Animal Coalition', 'cac-theme' ); ?>">

    <!-- Mobile: image renders first via CSS order -->
    <div class="hero__image" role="img" aria-label="<?php esc_attr_e( 'A golden retriever and tabby cat resting together', 'cac-theme' ); ?>"
         style="background-image: url('<?php echo esc_url( $hero_image_url ); ?>');">
    </div>

    <div class="hero__content">
        <h1 class="hero__title">
            <?php esc_html_e( 'Rescue.', 'cac-theme' ); ?><br>
            <?php esc_html_e( 'Rehabilitate.', 'cac-theme' ); ?><br>
            <?php esc_html_e( 'Rehome.', 'cac-theme' ); ?><br>
            <span class="hero__title-accent"><?php esc_html_e( 'Repeat.', 'cac-theme' ); ?></span>
        </h1>

        <p class="hero__subtitle">
            <?php esc_html_e( "We're building a community where every companion animal is valued, protected, and given the chance to thrive.", 'cac-theme' ); ?>
        </p>

        <div class="hero__actions">
            <a href="<?php echo $adopt_url; ?>" class="btn btn--primary btn--lg">
                <?php esc_html_e( 'Adopt a Pet', 'cac-theme' ); ?>
            </a>
            <a href="<?php echo $donate_url; ?>" class="btn btn--outline btn--lg">
                <?php esc_html_e( 'Donate Today', 'cac-theme' ); ?>
                <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </a>
        </div>
    </div>

</section>

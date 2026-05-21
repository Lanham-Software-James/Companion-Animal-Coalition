<?php
defined( 'ABSPATH' ) || exit;

$heading   = get_theme_mod( 'cac_page_cta_heading',   __( 'Ready to Make a Difference?', 'cac-theme' ) );
$text      = get_theme_mod( 'cac_page_cta_text',      __( 'There are so many ways you can help animals in need. Together, we can build a better future for every pet.', 'cac-theme' ) );
$btn_label = get_theme_mod( 'cac_page_cta_btn_label', __( 'Get Involved', 'cac-theme' ) );
$btn_url   = get_theme_mod( 'cac_page_cta_btn_url',   '/get-involved' );
?>

<section class="page-cta" aria-labelledby="page-cta-heading">
    <div class="page-cta__inner">

        <div class="page-cta__icon" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" width="56" height="56">
                <path d="M24 14c-1.2-4-4.8-7-8.8-7A8.8 8.8 0 0 0 6.4 15.8C6.4 25.2 24 37 24 37S41.6 25.2 41.6 15.8A8.8 8.8 0 0 0 32.8 7C28.8 7 25.2 10 24 14z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10 32c-2 1.5-5 4-7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M38 32c2 1.5 5 4 7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="page-cta__body">
            <h2 id="page-cta-heading" class="page-cta__heading">
                <?php echo esc_html( $heading ); ?>
            </h2>
            <p class="page-cta__text">
                <?php echo esc_html( $text ); ?>
            </p>
        </div>

        <a href="<?php echo esc_url( $btn_url ); ?>"
           class="btn btn--outline-white btn--lg page-cta__btn">
            <?php echo esc_html( $btn_label ); ?>
            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </a>

    </div>
</section>

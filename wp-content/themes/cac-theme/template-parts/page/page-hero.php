<?php
defined( 'ABSPATH' ) || exit;

$img_id  = get_post_thumbnail_id();
$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
$img_alt = $img_id ? trim( get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ) : '';
if ( ! $img_alt ) {
    $img_alt = get_the_title();
}
?>

<section class="page-hero" aria-labelledby="page-hero-title">

    <div class="page-hero__content">
        <?php cac_breadcrumb(); ?>

        <h1 id="page-hero-title" class="page-hero__title">
            <?php the_title(); ?>
        </h1>

        <?php
        $subheader = get_post_meta( get_the_ID(), 'cac_page_subheader', true );
        if ( $subheader ) : ?>
            <p class="page-hero__subheader">
                <?php echo esc_html( $subheader ); ?>
            </p>
        <?php endif; ?>

        <?php
        $intro = get_post_meta( get_the_ID(), 'cac_page_intro', true );
        if ( $intro ) : ?>
            <p class="page-hero__intro">
                <?php echo esc_html( $intro ); ?>
            </p>
        <?php endif; ?>

        <?php
        $btn_label = get_post_meta( get_the_ID(), 'cac_page_hero_btn_label', true );
        $btn_url   = get_post_meta( get_the_ID(), 'cac_page_hero_btn_url', true );
        if ( $btn_label && $btn_url ) : ?>
            <a href="<?php echo esc_url( $btn_url ); ?>" class="btn btn--primary page-hero__btn">
                <?php echo esc_html( $btn_label ); ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="page-hero__image"
         role="img"
         aria-label="<?php echo esc_attr( $img_alt ); ?>"
         <?php if ( $img_url ) : ?>
             style="background-image: url('<?php echo esc_url( $img_url ); ?>')"
         <?php endif; ?>>
    </div>

</section>

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

        <?php if ( has_excerpt() ) : ?>
            <p class="page-hero__intro">
                <?php echo wp_kses_post( get_the_excerpt() ); ?>
            </p>
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

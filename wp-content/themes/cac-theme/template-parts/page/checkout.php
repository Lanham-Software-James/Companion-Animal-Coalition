<?php
/** Cart and checkout shell. WooCommerce retains ownership of forms, payments and endpoints. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main cac-checkout<?php echo is_cart() ? ' cac-cart' : ''; ?>">
    <div class="container">
        <?php while ( have_posts() ) : the_post(); ?>
            <header class="cac-checkout__header">
                <?php cac_breadcrumb(); ?>
                <h1 class="page-hero__title"><?php the_title(); ?></h1>
            </header>
            <div class="cac-checkout__content">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer();

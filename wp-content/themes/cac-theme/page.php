<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main id="main-content" class="site-main page-template">

    <?php while ( have_posts() ) : the_post(); ?>

        <?php get_template_part( 'template-parts/page/page-hero' ); ?>

        <div class="page-body">
            <?php the_content(); ?>
        </div>

        <?php get_template_part( 'template-parts/page/page-cta' ); ?>

    <?php endwhile; ?>

</main>

<?php get_footer();

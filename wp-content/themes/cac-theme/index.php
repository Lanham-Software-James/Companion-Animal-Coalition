<?php
get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Latest News', 'cac-theme' ); ?></h1>
            </header>

            <div class="posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a class="post-card__image-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                <?php the_post_thumbnail( 'medium_large', [ 'class' => 'post-card__image' ] ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="post-card__body">
                            <div class="post-card__meta">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>
                            <h2 class="post-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="post-card__excerpt"><?php the_excerpt(); ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn btn--outline">
                                <?php esc_html_e( 'Read More', 'cac-theme' ); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination( [
                'mid_size'  => 2,
                'prev_text' => '&larr; ' . __( 'Previous', 'cac-theme' ),
                'next_text' => __( 'Next', 'cac-theme' ) . ' &rarr;',
            ] ); ?>

        <?php else : ?>
            <p><?php esc_html_e( 'No posts found.', 'cac-theme' ); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php get_footer();

<?php $stats = cac_get_impact_stats(); ?>

<section class="impact-stats" aria-labelledby="impact-stats-heading">
    <div class="container">

        <h2 class="impact-stats__heading" id="impact-stats-heading">
            <?php esc_html_e( 'Making an Impact Together', 'cac-theme' ); ?>
        </h2>

        <ul class="impact-stats__list" role="list">
            <?php foreach ( $stats as $stat ) : ?>
                <li class="impact-stats__item">
                    <div class="impact-stats__icon" aria-hidden="true">
                        <?php echo $stat['icon']; // SVG is pre-sanitized in functions.php ?>
                    </div>
                    <p class="impact-stats__number js-counter" data-target="<?php echo esc_attr( $stat['number'] ); ?>">
                        <?php echo esc_html( $stat['number'] ); ?>
                    </p>
                    <p class="impact-stats__label"><?php echo esc_html( $stat['label'] ); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>

<?php
$cards_defaults = [
    1 => [ 'title' => 'Volunteer', 'description' => 'Give your time and skills to help care for animals and support our mission.',                   'link_text' => 'Learn More',      'link_url' => '/volunteer' ],
    2 => [ 'title' => 'Foster',    'description' => 'Open your home temporarily to an animal in need while they await their forever family.',        'link_text' => 'Become a Foster', 'link_url' => '/foster'    ],
    3 => [ 'title' => 'Donate',    'description' => 'Your financial support funds medical care, food, and shelter for animals in our care.',          'link_text' => 'Donate Now',      'link_url' => '/donate'    ],
    4 => [ 'title' => 'Events',    'description' => 'Join us for adoption events, fundraisers, and community gatherings throughout the year.',        'link_text' => 'View Events',     'link_url' => '/events'    ],
];
$cards = [];
for ( $i = 1; $i <= 4; $i++ ) {
    $cards[ $i ] = [
        'title'       => get_theme_mod( "cac_card_{$i}_title",       $cards_defaults[ $i ]['title'] ),
        'description' => get_theme_mod( "cac_card_{$i}_description", $cards_defaults[ $i ]['description'] ),
        'link_text'   => get_theme_mod( "cac_card_{$i}_link_text",   $cards_defaults[ $i ]['link_text'] ),
        'link_url'    => get_theme_mod( "cac_card_{$i}_link_url",    $cards_defaults[ $i ]['link_url'] ),
    ];
}

$section_heading    = get_theme_mod( 'cac_get_involved_heading',    __( 'Get Involved', 'cac-theme' ) );
$section_subheading = get_theme_mod( 'cac_get_involved_subheading', __( 'There are so many ways to help animals in need.', 'cac-theme' ) );

$arrow_svg = '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
?>

<section class="get-involved" aria-labelledby="get-involved-heading">
    <div class="container">

        <header class="section-header section-header--centered">
            <h2 class="section-heading" id="get-involved-heading">
                <?php echo esc_html( $section_heading ); ?>
            </h2>
            <p class="section-subheading">
                <?php echo esc_html( $section_subheading ); ?>
            </p>
        </header>

        <ul class="get-involved__list" role="list">

            <li class="get-involved__item">
                <div class="get-involved__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M40.84 10.61a11 11 0 0 0-15.56 0L24 11.67l-1.28-1.06a11 11 0 0 0-15.56 15.56l1.06 1.06L24 43.23l15.78-15.9 1.06-1.06a11 11 0 0 0 0-15.66z"/>
                    </svg>
                </div>
                <h3 class="get-involved__title"><?php echo esc_html( $cards[1]['title'] ); ?></h3>
                <p class="get-involved__description">
                    <?php echo esc_html( $cards[1]['description'] ); ?>
                </p>
                <a href="<?php echo esc_url( $cards[1]['link_url'] ); ?>" class="get-involved__link">
                    <?php echo esc_html( $cards[1]['link_text'] ); ?>
                    <?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </a>
            </li>

            <li class="get-involved__item">
                <div class="get-involved__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 20L24 6l18 14v22a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z"/>
                        <polyline points="18 44 18 26 30 26 30 44"/>
                        <circle cx="24" cy="20" r="4"/>
                    </svg>
                </div>
                <h3 class="get-involved__title"><?php echo esc_html( $cards[2]['title'] ); ?></h3>
                <p class="get-involved__description">
                    <?php echo esc_html( $cards[2]['description'] ); ?>
                </p>
                <a href="<?php echo esc_url( $cards[2]['link_url'] ); ?>" class="get-involved__link">
                    <?php echo esc_html( $cards[2]['link_text'] ); ?>
                    <?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </a>
            </li>

            <li class="get-involved__item">
                <div class="get-involved__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 16 22 10 40 10 40 28 34 28"/>
                        <rect x="8" y="20" width="26" height="24" rx="2"/>
                        <line x1="21" y1="32" x2="21" y2="26"/>
                        <line x1="18" y1="29" x2="24" y2="29"/>
                    </svg>
                </div>
                <h3 class="get-involved__title"><?php echo esc_html( $cards[3]['title'] ); ?></h3>
                <p class="get-involved__description">
                    <?php echo esc_html( $cards[3]['description'] ); ?>
                </p>
                <a href="<?php echo esc_url( $cards[3]['link_url'] ); ?>" class="get-involved__link">
                    <?php echo esc_html( $cards[3]['link_text'] ); ?>
                    <?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </a>
            </li>

            <li class="get-involved__item">
                <div class="get-involved__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="10" width="36" height="34" rx="2"/>
                        <line x1="32" y1="4" x2="32" y2="16"/>
                        <line x1="16" y1="4" x2="16" y2="16"/>
                        <line x1="6" y1="24" x2="42" y2="24"/>
                        <rect x="16" y="30" width="6" height="6" rx="1"/>
                    </svg>
                </div>
                <h3 class="get-involved__title"><?php echo esc_html( $cards[4]['title'] ); ?></h3>
                <p class="get-involved__description">
                    <?php echo esc_html( $cards[4]['description'] ); ?>
                </p>
                <a href="<?php echo esc_url( $cards[4]['link_url'] ); ?>" class="get-involved__link">
                    <?php echo esc_html( $cards[4]['link_text'] ); ?>
                    <?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </a>
            </li>

        </ul>
    </div>
</section>

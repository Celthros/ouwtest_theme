<?php

pageBanner([
        'title'    => 'All Events',
        'subtitle' => 'See what is going on in our world.'
]);
?>

    <div class="container container--narrow page-section">
        <?php
        $eventQuery = new WP_Query([
                'post_type' => 'event',
                'posts_per_page' => -1
        ]);

        while ($eventQuery->have_posts()) {
            $eventQuery->the_post();
            get_template_part('template-parts/content-event');
        }
        echo paginate_links([
                'total' => $eventQuery->max_num_pages
        ]);
        wp_reset_postdata();
        ?>

        <hr class="section-break">

        <p>Looking for a recap of past events? <a href="<?php echo site_url('/past-events') ?>">Check out our past events archive</a>.</p>
    </div>
<?php

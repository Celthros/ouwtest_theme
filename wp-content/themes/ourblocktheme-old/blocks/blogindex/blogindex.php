<?php

pageBanner(array(
        'title' => 'Welcome to our blog!',
        'subtitle' => 'Keep up with our latest news.'
));
?>
<div class="container container--narrow page-section">
    <?php while (have_posts()) : the_post(); ?>
        <div class="post-item">
            <h2 class="headline headline--medium headline--post-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>

            <div class="metabox">
                <?php
                $author_link = get_the_author_posts_link();
                $post_time = get_the_time('n.j.y');
                $category_list = get_the_category_list(', ');
                ?>
                <p>Posted by <?php echo $author_link; ?> on <?php echo $post_time; ?> in <?php echo $category_list; ?></p>
            </div>

            <div class="generic-content">
                <?php the_excerpt(); ?>
                <p><a class="btn btn--blue" href="<?php the_permalink(); ?>">Continue reading &raquo;</a></p>
            </div>
        </div>
    <?php endwhile; ?>
    <?php echo paginate_links(); ?>
</div>

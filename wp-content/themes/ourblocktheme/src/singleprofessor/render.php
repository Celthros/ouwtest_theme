<?php

use \Ourblocktheme\controllers\Program as controllerProgram;
use \Ourblocktheme\controllers\LikeManager as LikeManager;

$likeCount   = LikeManager::getLikeCount();

pageBanner();
?>

<div class="container container--narrow page-section">

    <div class="generic-content">
        <div class="row group">

            <div class="one-third">
				<?php the_post_thumbnail( 'professorPortrait' ); ?>
            </div>

            <div class="two-thirds">
				<?php if ( $likeCount ) {
					$existStatus = LikeManager::likeStatus();
                    ?>
                    <span class="like-box"
                          data-like="<?php echo isset( $likeCount->posts[0]->ID ) ? esc_attr( $likeCount->posts[0]->ID ) : ''; ?>"
                          data-professor="<?php the_ID(); ?>"
                          data-exists="<?php echo esc_attr( $existStatus ); ?>">
                    <i class="fa fa-heart-o" aria-hidden="true"></i>
                    <i class="fa fa-heart" aria-hidden="true"></i>
                    <span class="like-count"><?php echo isset( $likeCount->found_posts ) ? esc_html( $likeCount->found_posts ) : 0; ?></span>
                </span>
				<?php } ?>
				<?php the_content(); ?>
            </div>

        </div>
    </div>

	<?php
	$relatedPrograms = controllerProgram::getRelatedPrograms();

	if ( $relatedPrograms ) {
		$subTxt = controllerProgram::is_Related_txt( 2 );

        ?>
        <hr class="section-break">
        <h2 class="headline headline--medium"><?php echo $subTxt;?> Taught</h2>
        <ul class="link-list min-list">
			<?php foreach ( $relatedPrograms as $program ) : ?>
                <li>
                    <a href="<?php echo esc_url( get_the_permalink( $program ) ); ?>"><?php echo esc_html( get_the_title( $program ) ); ?></a>
                </li>
			<?php endforeach; ?>
        </ul>
	<?php } ?>

</div>
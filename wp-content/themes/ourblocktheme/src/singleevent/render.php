<?php

pageBanner();
?>

<div class="container container--narrow page-section">
	<div class="metabox metabox--position-up metabox--with-home-link">
		<p><a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link( 'event' ); ?>"><i
					class="fa fa-home" aria-hidden="true"></i> Events Home</a> <span
				class="metabox__main"><?php the_title(); ?></span></p>
	</div>

	<div class="generic-content"><?php the_content(); ?></div>

	<?php

	$relatedPrograms = get_field( 'related_programs' );

	if ( $relatedPrograms ):
		$count = count( $relatedPrograms );
		$programText = 'Program';
		if ( $count > 1 ) {
			$programText = $programText . 's';
		}
		?>
		<hr class="section-break">
		<h2 class="headline headline--medium">Related <?php echo $programText; ?></h2>
		<ul class="link-list min-list">
			<?php foreach ( $relatedPrograms as $program ) : ?>
				<li><a href="<?php echo get_the_permalink( $program ); ?>"><?php echo get_the_title( $program ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div>
<?php

use \Ourblocktheme\controllers\Event as EventController;

$pastEvents = EventController::pastEvents();

pageBanner( array(
	'title'    => 'Past Events',
	'subtitle' => 'A recap of our past events.'
) );
?>

<div class="container container--narrow page-section">
	<?php

	while ( $pastEvents->have_posts() ) {
		$pastEvents->the_post();
		get_template_part( 'template-parts/content-event' );
	}
	echo paginate_links( array(
		'total' => $pastEvents->max_num_pages
	) );
	?>
</div>
<?php

pageBanner( array(
	'title'    => 'Our Campuses',
	'subtitle' => 'We have several conveniently located campuses.',
) );
?>

<div class="container page-section">

	<div class="acf-map">

		<?php
		while ( have_posts() ) {
			the_post();
			$mapLocation = get_field( 'map_location' );

			if ( ! is_array( $mapLocation ) || ! isset( $mapLocation['lat'], $mapLocation['lng'] ) ) {
				continue;
			}
			?>
			<div class="marker" data-lat="<?php echo esc_attr( $mapLocation['lat'] ); ?>" data-lng="<?php echo esc_attr( $mapLocation['lng'] ); ?>">
				<h3 class="fontTitleSmall"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<p class="fontBodyMedium"><?php echo esc_html( $mapLocation['address'] ?? '' ); ?></p>
			</div>
		<?php } ?>
	</div>


</div>


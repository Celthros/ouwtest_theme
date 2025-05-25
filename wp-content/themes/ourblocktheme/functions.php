<?php

/*
 * Version 1 of classname conversion
 *
 */

require_once get_theme_file_path( 'autoload.php' );

function pageBanner( $args = null ): void {

	if ( ! isset( $args['title'] ) ) {
		$args['title'] = get_the_title() ?? get_bloginfo( 'name' );
	}

	if ( ! isset( $args['subtitle'] ) ) {
		$args['subtitle'] = get_field( 'page_banner_subtitle' ) ?? get_bloginfo( 'description' ) ?? 'Welcome to our university';
	}

	if ( ! isset( $args['photo'] ) ) {
		$args['photo'] = get_theme_file_uri( '/images/ocean.jpg' ) ?? '';
		if ( get_field( 'page_banner_background_image' ) && ! is_archive() && ! is_home() ) {
			$args['photo'] = get_field( 'page_banner_background_image' )['sizes']['pageBanner'] ?? '';
		}
	}

	?>
	<div class="page-banner">
		<div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
		<div class="page-banner__content container container--narrow">
			<h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
			<div class="page-banner__intro">
				<p><?php echo $args['subtitle']; ?></p>
			</div>
		</div>
	</div>
<?php }

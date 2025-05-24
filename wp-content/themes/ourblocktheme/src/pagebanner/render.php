<?php

if ( ! isset( $args['title'] ) ) {
	$args['title'] = esc_html( get_the_title() ?? get_bloginfo( 'name' ) );
}

if ( ! isset( $args['subtitle'] ) ) {
	$args['subtitle'] = function_exists( 'get_field' )
		? esc_html( get_field( 'page_banner_subtitle' ) ?? get_bloginfo( 'description' ) ?? 'Welcome to our university' )
		: esc_html( get_bloginfo( 'description' ) ?? 'Welcome to our university' );
}

if ( ! isset( $args['photo'] ) ) {
	$args['photo'] = esc_url( get_theme_file_uri( '/images/ocean.jpg' ) );
	if ( function_exists( 'get_field' ) && get_field( 'page_banner_background_image' ) && ! is_archive() && ! is_home() ) {
		$args['photo'] = esc_url( get_field( 'page_banner_background_image' )['sizes']['pageBanner'] ?? get_theme_file_uri( '/images/ocean.jpg' ) );
	}
}

?>
<div class="page-banner">
    <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
    <div class="page-banner__content container container--narrow">
        <h1 class="page-banner__title"><?php echo $args['title']; ?></h1>
        <div class="page-banner__intro">
            <p><?php echo $args['subtitle']; ?></p>
        </div>
    </div>
</div>
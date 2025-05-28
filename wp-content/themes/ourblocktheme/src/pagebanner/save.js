import React from "react";
import { select } from "@wordpress/data";

export default function save ( { attributes } ) {
	// Set defaults using JS logic, similar to the original PHP
	const title =
		attributes.title ||
		select ( "core/editor" ).getCurrentPost ()?.title ||
		select ( "core" ).getSite ()?.name ||
		"";

	const subtitle =
		attributes.subtitle ||
		( typeof acf !== "undefined" && acf.getField ( "page_banner_subtitle" ) ) ||
		select ( "core" ).getSite ()?.description ||
		"Welcome to our university";

	let photo =
		attributes.photo ||
		( typeof wp !== "undefined" &&
			wp.theme &&
			wp.theme.getThemeFileUri &&
			wp.theme.getThemeFileUri ( "/images/ocean.jpg" ) );

	// If ACF background image is set, use it
	if (
		typeof acf !== "undefined" &&
		acf.getField ( "page_banner_background_image" ) &&
		! select ( "core/editor" ).getCurrentPost ()?.type?.includes ( "archive" ) &&
		! select ( "core/editor" ).getCurrentPost ()?.type?.includes ( "home" )
	) {
		const bg = acf.getField ( "page_banner_background_image" );
		photo = ( bg.sizes && bg.sizes.pageBanner ) || photo;
	}

	return (
		<div className="page-banner">
			<div
				className="page-banner__bg-image"
				style={ { backgroundImage : `url(${ photo })` } }
			></div>
			<div className="page-banner__content container container--narrow">
				<h1 className="page-banner__title">{ title }</h1>
				<div className="page-banner__intro">
					<p>{ subtitle }</p>
				</div>
			</div>
		</div>
	);
}

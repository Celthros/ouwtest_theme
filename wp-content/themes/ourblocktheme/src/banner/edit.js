import apiFetch from '@wordpress/api-fetch';
import { Button, PanelBody, PanelRow } from '@wordpress/components';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

export default function Edit( props ) {
	const blockProps = useBlockProps();
	const title = metadata.title;

	useEffect(() => {
		if ( ! props.attributes.imgURL ) {
			props.setAttributes ( {
				imgURL: ourThemeData.themePath + "/images/library-hero.jpg"
			} );
		}
	}, []);

	useEffect(
		function () {
			if ( props.attributes.imgID ) {
				( async () => {
					const response = await apiFetch( {
						path: `/wp/v2/media/${ props.attributes.imgID }`,
						method: 'GET',
					} );
					props.setAttributes( {
						imgURL: response.media_details.sizes.pageBanner
							.source_url,
					} );
				} )();
			}
		},
		[ props.attributes.imgID ]
	);

	function onFileSelect( x ) {
		props.setAttributes( { imgID: x.id } );
	}

	return (
		<div { ...blockProps }>
			<div className="our-placeholder-block">
				<InspectorControls>
					<PanelBody title="Background" initialOpen={ true }>
						<PanelRow>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ onFileSelect }
									value={ props.attributes.imgID }
									render={ ( { open } ) => {
										return (
											<Button onClick={ open }>
												Choose Image
											</Button>
										);
									} }
								/>
							</MediaUploadCheck>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
				<div className="page-banner">
					<div
						className="page-banner__bg-image"
						style={ {
							backgroundImage: `url('${ props.attributes.imgURL }')`,
						} }
					></div>
					<div className="page-banner__content container t-center c-white">
						<InnerBlocks
							allowedBlocks={ [
								'ourblocktheme/genericheading',
								'ourblocktheme/genericbutton',
							] }
						/>
					</div>
				</div>
			</div>
		</div>
	);
}

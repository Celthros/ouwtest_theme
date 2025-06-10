import { InspectorControls , MediaUpload , useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { ColorPalette , PanelBody , PanelRow , TextControl , Button } from "@wordpress/components";


export default function Edit ( { attributes , setAttributes } ) {
	const blockProps = useBlockProps ();
	const title = metadata.title;

	return (
		<div { ...blockProps }>
			<div className="our-placeholder-block">
				{ __ ( title , 'ourblocktheme' ) }
			</div>

			<InspectorControls>
				<PanelBody title={ __ ( 'Banner Settings' , 'ourblocktheme' ) }>

					<TextControl
						label={ __ ( 'Title' , 'ourblocktheme' ) }
						value={ attributes.title || '' }
						onChange={ ( value ) => setAttributes ( { title : value } ) }
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
					<TextControl
						label={ __ ( 'Subtitle' , 'ourblocktheme' ) }
						value={ attributes.subtitle || '' }
						onChange={ ( value ) => setAttributes ( { subtitle : value } ) }
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
					<MediaUpload
						onSelect={ ( media ) => setAttributes ( { photo : media.url } ) }
						allowedTypes={ [ 'image' ] }
						render={ ( { open } ) => (
							<Button
								variant="secondary"
								onClick={ open }>
								{ attributes.photo ? (
									<img src={ attributes.photo }
									     style={{ maxWidth: "300px" }}
									     className="wp-block-image aligncenter"
									     alt={ __ ( 'Selected photo' , 'ourblocktheme' ) }/>
								) : (
									__ ( 'Upload photo' , 'ourblocktheme' )
								) }
							</Button>
						) }
					/>

				</PanelBody>
			</InspectorControls>
		</div>
	);
}

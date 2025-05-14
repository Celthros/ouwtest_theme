import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit( props ) {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div style={ { backgroundColor: '#333', padding: '35px ' } }>
				<p
					style={ {
						textAlign: 'center',
						fontSize: '20px',
						color: '#ffffff',
					} }
				>
					{ __( 'Converted Slideshow', 'ourblocktheme' ) }
				</p>
				<InnerBlocks allowedBlocks={ [ 'ourblocktheme/slide' ] } />
			</div>
		</div>
	);
}

import { InnerBlocks } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType( 'ourblocktheme/slideshow', {
	title: 'Slideshow',
	supports: {
		align: [ 'full' ],
	},
	attributes: {
		align: { type: 'string', default: 'full' },
	},
	edit: EditComponent,
	save: SaveComponent,
} );

function EditComponent() {
	return (
		<div style={ { backgroundColor: '#333', padding: '35px ' } }>
			<p
				style={ {
					textAlign: 'center',
					fontSize: '20px',
					color: '#ffffff',
				} }
			>
				Slideshow
			</p>
			<InnerBlocks allowedBlocks={ [ 'ourblocktheme/slide' ] } />
		</div>
	);
}

function SaveComponent( props ) {
	return <InnerBlocks.Content />;
}

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import { RichText } from '@wordpress/block-editor';

function Save( props ) {
	function createTagName() {
		switch ( props.attributes.size ) {
			case 'large':
				return 'h1';
			case 'medium':
				return 'h2';
			case 'small':
				return 'h3';
		}
	}
	return (
		<RichText.Content
			tagName={ createTagName() }
			className={ `headline headline--${ props.attributes.size }` }
			value={ props.attributes.text }
		/>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: Save,
} );

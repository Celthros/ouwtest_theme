import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import Save from './save';
import { InnerBlocks } from '@wordpress/block-editor';

registerBlockType( metadata.name, {
	edit: Edit,
	//save: Save,
} );

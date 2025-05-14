import {
	useBlockProps,
	RichText,
	BlockControls,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerBlockType } from '@wordpress/blocks';

export default function Edit( props ) {
	const blockProps = useBlockProps();
	const title = metadata.title;

	function handleTextChange( x ) {
		props.setAttributes( { text: x } );
	}
	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						isPressed={ props.attributes.size === 'large' }
						onClick={ () =>
							props.setAttributes( { size: 'large' } )
						}
					>
						Large
					</ToolbarButton>
					<ToolbarButton
						isPressed={ props.attributes.size === 'medium' }
						onClick={ () =>
							props.setAttributes( { size: 'medium' } )
						}
					>
						Medium
					</ToolbarButton>
					<ToolbarButton
						isPressed={ props.attributes.size === 'small' }
						onClick={ () =>
							props.setAttributes( { size: 'small' } )
						}
					>
						Small
					</ToolbarButton>
				</ToolbarGroup>
			</BlockControls>
			<RichText
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
				tagName="h1"
				className={ `headline headline--${ props.attributes.size }` }
				value={ props.attributes.text }
				onChange={ handleTextChange }
			/>
		</div>
	);
}

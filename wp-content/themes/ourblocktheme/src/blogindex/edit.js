import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';

export default function Edit() {
	const blockProps = useBlockProps();
	const title = metadata.title;

	return (
		<div { ...blockProps }>
			<div className="our-placeholder-block">
				{ __( title, 'ourblocktheme' ) }
			</div>
		</div>
	);
}

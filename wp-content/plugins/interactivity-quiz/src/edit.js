import {
	TextControl ,
	Flex ,
	FlexBlock ,
	FlexItem ,
	Button ,
	Icon ,
	PanelBody ,
	PanelRow ,
	ColorPicker
} from "@wordpress/components"
import { InspectorControls , BlockControls , AlignmentToolbar , useBlockProps } from "@wordpress/block-editor";
import { starFilled , starEmpty } from '@wordpress/icons';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {Element} Element to render.
 */
export default function Edit ( { attributes , setAttributes } ) {
	const blockProps = useBlockProps ();

	function updateQuestion ( value ) {
		setAttributes ( { question : value } )
	}

	function deleteAnswer ( indexToDelete ) {
		const newAnswers = attributes.answers.filter ( function ( x , index ) {
			return index !== indexToDelete
		} )
		setAttributes ( { answers : newAnswers } )

		if ( indexToDelete === attributes.correctAnswer ) {
			setAttributes ( { correctAnswer : undefined } )
		}
	}

	function markAsCorrect ( index ) {
		setAttributes ( { correctAnswer : index } )
	}

	return (
		<div { ...blockProps }>
			<div
				className="paying-attention-edit-block"
				style={ { backgroundColor : attributes.bgColor } }
			>
				<BlockControls>
					<AlignmentToolbar
						value={ attributes.theAlignment }
						onChange={ x => setAttributes ( { theAlignment : x } ) }
					/>
				</BlockControls>
				<InspectorControls>
					<PanelBody title="Background Color" initialOpen={ true }>
						<PanelRow>
							<ColorPicker
								color={ attributes.bgColor }
								onChangeComplete={ x => setAttributes ( { bgColor : x.hex } ) }
								disableAlpha={ true }
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
				<TextControl
					label={ __ ( "Question:" , "interactivity-quiz" ) }
					value={ attributes.question }
					onChange={ updateQuestion }
					style={ { fontSize : "20px" } }
					__next40pxDefaultSize={ true }
					__nextHasNoMarginBottom={ true }
				/>
				<p style={ { fontSize : "13px" , margin : "20px 0 8px 0" } }>
					{ __ ( "Answers:" , "interactivity-quiz" ) }
				</p>
				{ attributes.answers.map ( function ( answer , index ) {
					return (
						<Flex>
							<FlexBlock>
								<TextControl
									autoFocus={ answer === undefined }
									value={ answer }
									onChange={ ( newValue ) => {
										const newAnswers = attributes.answers.concat ( [] );
										newAnswers[ index ] = newValue;
										setAttributes ( { answers : newAnswers } );
									} }
									__next40pxDefaultSize={ true }
									__nextHasNoMarginBottom={ true }
								/>
							</FlexBlock>
							<FlexItem>
								<Button onClick={ () => markAsCorrect ( index ) } style={ { marginTop : "20px" } }>
									<Icon
										className="mark-as-correct"
										icon={ attributes.correctAnswer === index ? starFilled : starEmpty }
									/>
								</Button>
							</FlexItem>
							<FlexItem>
								<Button isLink className="attention-delete" onClick={ () => deleteAnswer ( index ) }
								        style={ { marginTop : "10px" } }>
									Delete
								</Button>
							</FlexItem>
						</Flex>
					)
				} ) }
				<Button
					isPrimary
					onClick={ () => {
						setAttributes ( { answers : attributes.answers.concat ( [ undefined ] ) } )
					} }
				>
					{ __ ( "Add another answer" , "interactivity-quiz" ) }
				</Button>
			</div>
		</div>
	);
}

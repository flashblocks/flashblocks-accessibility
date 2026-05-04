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
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

import { TextControl, PanelBody } from '@wordpress/components';
import Button from './Button';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   root0               Component props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { selector, playText, pauseText } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<Button
				blockProps={ blockProps }
				playText={ playText }
				pauseText={ pauseText }
				selector={ selector }
			/>

			<InspectorControls>
				<PanelBody
					title={ __( 'Settings', 'flashblocks-accessibility' ) }
				>
					<TextControl
						label={ __(
							'Query Selector',
							'flashblocks-accessibility'
						) }
						value={ attributes.selector }
						onChange={ ( nextSelector ) =>
							setAttributes( { selector: nextSelector } )
						}
						help={ __(
							'Enter a query selector to target a video. Leave blank to target the closest video.',
							'flashblocks-accessibility'
						) }
					/>
					<TextControl
						label={ __(
							'Play Text or SVG',
							'flashblocks-accessibility'
						) }
						value={ attributes.playText }
						onChange={ ( nextPlayText ) =>
							setAttributes( { playText: nextPlayText } )
						}
						help={ __(
							'Enter the text or SVG for the play button.',
							'flashblocks-accessibility'
						) }
					/>
					<TextControl
						label={ __(
							'Pause Text or SVG',
							'flashblocks-accessibility'
						) }
						value={ attributes.pauseText }
						onChange={ ( nextPauseText ) =>
							setAttributes( { pauseText: nextPauseText } )
						}
						help={ __(
							'Enter the text or SVG for the pause button.',
							'flashblocks-accessibility'
						) }
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}

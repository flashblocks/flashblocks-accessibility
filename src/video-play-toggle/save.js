/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import Button from './Button';
/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @param {Object} root0            Component props.
 * @param {Object} root0.attributes Block attributes.
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
	const { selector, playText, pauseText } = attributes;
	const blockProps = useBlockProps.save();

	return (
		<Button
			blockProps={ blockProps }
			playText={ playText }
			pauseText={ pauseText }
			selector={ selector }
		/>
	);
}

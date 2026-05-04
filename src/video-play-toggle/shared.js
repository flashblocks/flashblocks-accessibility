/**
 * Builds CSS custom properties from block style attributes.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Block wrapper props.
 */
export function getBlockProps( attributes ) {
	const bgColor = getCssVarValue(
		attributes?.style?.elements?.button?.color?.background
	);
	const textColor = getCssVarValue(
		attributes?.style?.elements?.button?.color?.text
	);

	const obj = {
		style: {
			'--bg-color': bgColor,
			'--text-color': textColor,
		},
	};

	return obj;
}

/**
 * Generates a CSS variable string from a preset string.
 *
 * Example Input: 'var:preset|color|background'
 * Example Output: 'var(--wp--preset--color--background)'
 *
 * @param {string} styleValue Style value to normalize.
 * @return {string} Normalized style value.
 */
function getCssVarValue( styleValue ) {
	if ( ! styleValue?.startsWith( 'var:preset|' ) ) {
		return styleValue;
	}

	const parts = styleValue.split( '|' );

	return `var(--wp--preset--${ parts[ 1 ] }--${ parts[ 2 ] })`;
}

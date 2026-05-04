export default function Button( {
	blockProps,
	playText,
	pauseText,
	selector,
} ) {
	return (
		<button
			{ ...blockProps }
			data-play-text={ playText }
			data-pause-text={ pauseText }
			data-selector={ selector }
			aria-label="Play video"
			dangerouslySetInnerHTML={ { __html: playText || 'Play' } }
		/>
	);
}

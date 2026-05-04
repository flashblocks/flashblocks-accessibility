document.addEventListener( 'DOMContentLoaded', () => {
	document
		.querySelectorAll(
			'.wp-block-flashblocks-video-play-toggle, .wp-block-flashblocks-video-controls'
		)
		.forEach( ( button ) => {
			const selector = button.dataset.selector;
			let video = selector
				? document.querySelector( selector )
				: button.closest( '.wp-block-cover' )?.querySelector( 'video' );

			if ( video && video.tagName.toLowerCase() !== 'video' ) {
				video = video.querySelector( 'video' );
			}

			if ( ! video ) {
				return;
			}

			const togglePlay = () => {
				document
					.querySelectorAll( 'video' )
					.forEach( ( currentVideo ) => {
						if ( currentVideo !== video ) {
							currentVideo.pause();
						}
					} );

				if ( video.paused ) {
					video.play();
				} else {
					video.pause();
				}
			};

			updateButtonState( video, button );

			button.addEventListener( 'click', togglePlay );
			button.addEventListener( 'keydown', ( event ) => {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					togglePlay();
				}
			} );

			video.addEventListener( 'play', () =>
				updateButtonState( video, button )
			);
			video.addEventListener( 'pause', () =>
				updateButtonState( video, button )
			);
		} );

	function updateButtonState( video, button ) {
		const isPlaying = ! video.paused;
		const playText = decodeHtml( button.dataset.playText || 'Play' );
		const pauseText = decodeHtml( button.dataset.pauseText || 'Pause' );

		button.innerHTML = isPlaying ? pauseText : playText;
		button.setAttribute( 'aria-pressed', String( isPlaying ) );
		button.setAttribute(
			'aria-label',
			isPlaying ? 'Pause video' : 'Play video'
		);
	}

	function decodeHtml( html ) {
		const textarea = document.createElement( 'textarea' );
		textarea.innerHTML = html;
		return textarea.value;
	}
} );

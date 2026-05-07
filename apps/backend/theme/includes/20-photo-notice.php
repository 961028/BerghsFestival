<?php

add_action(
	'acf/init',
	function () {
		_app_footer_text_block_register( 'photo-notice', 'Photo Notice', 'dashicons-camera', 31 );
	}
);

add_action(
	'rest_api_init',
	function () {
		_app_footer_text_block_register_rest( 'photo-notice' );
	}
);

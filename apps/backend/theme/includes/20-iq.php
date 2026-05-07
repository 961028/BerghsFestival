<?php

add_action(
	'acf/init',
	function () {
		_app_footer_text_block_register( 'iq', 'IQ', 'dashicons-beer', 30 );
	}
);

add_action(
	'rest_api_init',
	function () {
		_app_footer_text_block_register_rest( 'iq' );
	}
);

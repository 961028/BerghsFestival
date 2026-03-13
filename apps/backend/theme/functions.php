<?php

/**
 * Require ./includes in natural order.
 */
function _app_require_includes() {
	$files = glob( __DIR__ . '/includes/*' );

	// Can't trust that glob always returns files in same order
	// https://glotpress.trac.wordpress.org/ticket/211
	natsort( $files );

	foreach ( $files as $file ) {
		require_once $file;
	}
}

_app_require_includes();

return;

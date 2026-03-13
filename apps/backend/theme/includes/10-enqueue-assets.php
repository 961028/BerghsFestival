<?php

function _app_enqueue_scripts() {
	[$style_url, $style_version]   = _app_get_asset( 'style.css' );
	[$script_url, $script_version] = _app_get_asset( 'assets/js/main.js' );

	wp_enqueue_style( 'app-style', $style_url, array(), $style_version );
	wp_enqueue_script( 'app-scripts', $script_url, array(), $script_version, true );
}
add_action( 'wp_enqueue_scripts', '_app_enqueue_scripts' );

/**
 * Get the URL and version of a theme asset.
 *
 * @return array{string,string}
 */
function _app_get_asset( string $file ): array {
	$mtime = filemtime( __DIR__ . '/../' . $file );
	$url   = get_template_directory_uri() . '/' . $file;

	return array( $url, $mtime );
}

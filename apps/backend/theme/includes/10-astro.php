<?php

if ( wp_get_environment_type() !== 'local' ) {
	return;
}

function _app_astro_trigger_rebuild() {
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	file_put_contents( '/tmp/cms-updated.flag', time() );
}

function _app_astro_on_post_change( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	_app_astro_trigger_rebuild();
}
add_action( 'save_post', '_app_astro_on_post_change' );
add_action( 'delete_post', '_app_astro_on_post_change' );

function _app_astro_on_term_change() {
	_app_astro_trigger_rebuild();
}
add_action( 'created_term', '_app_astro_on_term_change' );
add_action( 'edited_term', '_app_astro_on_term_change' );
add_action( 'delete_term', '_app_astro_on_term_change' );


function _app_astro_on_meta_change( $meta_id, $object_id, $meta_key ) {
	$ignored_prefixes = array(
		'_transient_',
		'_edit_lock',
		'_edit_last',
		'_encloseme',
	);

	foreach ( $ignored_prefixes as $prefix ) {
		if ( str_starts_with( $meta_key, $prefix ) ) {
			return;
		}
	}

	_app_astro_trigger_rebuild();
}
add_action( 'added_post_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );
add_action( 'updated_post_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );
add_action( 'deleted_post_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );
add_action( 'added_term_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );
add_action( 'updated_term_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );
add_action( 'deleted_term_meta', '_app_astro_on_meta_change', PHP_INT_MAX, 3 );

function _app_astro_on_option_change( $option ) {
	$ignored_prefixes = array( '_transient_', '_site_transient_', 'cron', 'session_tokens' );

	foreach ( $ignored_prefixes as $prefix ) {
		if ( str_starts_with( $option, $prefix ) ) {
			return;
		}
	}
	_app_astro_trigger_rebuild();
}
add_action( 'updated_option', '_app_astro_on_option_change' );
add_action( 'added_option', '_app_astro_on_option_change' );
add_action( 'deleted_option', '_app_astro_on_option_change' );

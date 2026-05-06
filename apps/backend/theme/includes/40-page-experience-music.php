<?php

function _app_page_experience_music_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-experience-music' ), ...$slugs );
}

function _app_page_experience_music_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-experience-music' ), ...$slugs );
}

function _app_page_experience_music_register_fields() {
	$location = app_acf_get_page_template_location( 'page-experience-music' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_experience_music_group_key( 'music' ),
			'title'        => 'Music',
			'fields'       => array(),
			'location'     => array( $location ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_experience_music_register_fields' );

<?php

function _app_home_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_options_key_prefix( 'home' ), ...$slugs );
}

function _app_home_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_options_key_prefix( 'home' ), ...$slugs );
}

function _app_home_register_options() {
	acf_add_options_page(
		array(
			'page_title' => 'Home',
			'menu_title' => 'Home',
			'menu_slug'  => 'app-home',
			'icon_url'   => 'dashicons-admin-home',
			'position'   => '25',
			'capability' => 'manage_options',
		)
	);

	$location   = app_acf_get_options_page_location( 'app-home' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'        => _app_home_group_key(),
			'title'      => 'Content',
			'fields'     => array(
				array(
					'key'   => _app_home_field_key( 'meta-title' ),
					'label' => 'Meta Title',
					'name'  => 'home-meta-title',
					'type'  => 'text',
				),
				array(
					'key'          => _app_home_field_key( 'manifest' ),
					'label'        => 'Manifest',
					'name'         => 'home-manifest',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'          => _app_home_field_key( 'about' ),
					'label'        => 'About',
					'name'         => 'home-about',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
			),
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
		)
	);
}
add_action( 'acf/init', '_app_home_register_options' );

function _app_home_on_rest_api_init() {
	register_rest_route(
		'app/v1',
		'home',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => '_app_home_rest_api_callback',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', '_app_home_on_rest_api_init' );

function _app_home_get( string $name ): string {
	$value = get_field( 'home-' . $name, 'options' );

	if ( ! is_string( $value ) ) {
		return '';
	}

	return $value;
}

function _app_home_rest_api_callback(): WP_REST_Response {
	return rest_ensure_response(
		array(
			'meta_title' => _app_home_get( 'meta-title' ),
			'manifest'   => _app_home_get( 'manifest' ),
			'about'      => _app_home_get( 'about' ),
		)
	);
}

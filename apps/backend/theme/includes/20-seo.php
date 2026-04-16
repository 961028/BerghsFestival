<?php

function _app_seo_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_options_key_prefix( 'seo' ), ...$slugs );
}

function _app_seo_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_options_key_prefix( 'seo' ), ...$slugs );
}

function _app_seo_register_options() {
	acf_add_options_page(
		array(
			'page_title' => 'SEO Options',
			'menu_title' => 'SEO',
			'menu_slug'  => 'app-seo',
			'icon_url'   => 'dashicons-admin-links',
			'position'   => '30',
			'capability' => 'manage_options',
		)
	);

	$location   = app_acf_get_options_page_location( 'app-seo' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'          => _app_seo_group_key(),
			'title'        => 'Defaults',
			'fields'       => array(
				array(
					'key'         => _app_seo_field_key( 'seo', 'meta_description' ),
					'label'       => 'Meta Description',
					'name'        => 'seo-meta_description',
					'type'        => 'textarea',
					'maxlength'   => 155,
					'placeholder' => get_bloginfo( 'description' ),
				),
				array(
					'key'           => _app_seo_field_key( 'seo', 'og_image' ),
					'label'         => 'Image',
					'name'          => 'seo-og_image',
					'type'          => 'image',
					'return_format' => 'id',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_seo_register_options' );

function app_seo_get_meta_description(): string {
	$value = get_field( _app_seo_field_key( 'seo', 'meta_description' ), 'options' );

	if ( ! is_string( $value ) ) {
		return '';
	}

	return $value;
}

function _app_seo_get_og_image(): ?int {
	$value = get_field( _app_seo_field_key( 'seo', 'og_image' ), 'options' );

	if ( ! is_int( $value ) || $value <= 0 ) {
		return null;
	}

	return $value;
}

function _app_seo_on_rest_api_init() {
	register_rest_route(
		'app/v1',
		'seo',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => '_app_seo_rest_api_callback',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', '_app_seo_on_rest_api_init' );

function _app_seo_rest_api_callback(): WP_REST_Response {

	return rest_ensure_response(
		array(
			'meta_description' => app_seo_get_meta_description(),
			'og_image'         => _app_seo_get_og_image(),
		)
	);
}

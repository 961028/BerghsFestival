<?php

/**
 * Generic footer text block: an options page with a title + WYSIWYG content,
 * exposed via REST. Used for IQ, Photo Notice, etc.
 */

function _app_footer_text_block_group_key( string $slug, string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_options_key_prefix( $slug ), ...$slugs );
}

function _app_footer_text_block_field_key( string $slug, string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_options_key_prefix( $slug ), ...$slugs );
}

function _app_footer_text_block_register( string $slug, string $label, string $icon, int $position ) {
	acf_add_options_page(
		array(
			'page_title' => $label,
			'menu_title' => $label,
			'menu_slug'  => "app-$slug",
			'icon_url'   => $icon,
			'position'   => (string) $position,
			'capability' => 'manage_options',
		)
	);

	$location   = app_acf_get_options_page_location( "app-$slug" );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'        => _app_footer_text_block_group_key( $slug ),
			'title'      => 'Content',
			'fields'     => array(
				array(
					'key'   => _app_footer_text_block_field_key( $slug, 'title' ),
					'label' => 'Title',
					'name'  => "$slug-title",
					'type'  => 'text',
				),
				array(
					'key'          => _app_footer_text_block_field_key( $slug, 'content' ),
					'label'        => '',
					'name'         => "$slug-content",
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

function _app_footer_text_block_get( string $slug, string $name ): string {
	$value = get_field( "$slug-" . $name, 'options' );

	if ( ! is_string( $value ) ) {
		return '';
	}

	return $value;
}

function _app_footer_text_block_register_rest( string $slug ) {
	register_rest_route(
		'app/v1',
		$slug,
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => function () use ( $slug ): WP_REST_Response {
				return rest_ensure_response(
					array(
						'title'   => _app_footer_text_block_get( $slug, 'title' ),
						'content' => _app_footer_text_block_get( $slug, 'content' ),
					)
				);
			},
			'permission_callback' => '__return_true',
		)
	);
}

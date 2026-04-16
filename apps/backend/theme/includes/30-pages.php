<?php

function _app_page_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_post_type_key_prefix( 'page' ), ...$slugs );
}

function _app_page_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_post_type_key_prefix( 'page' ), ...$slugs );
}

function _app_page_register_fields() {
	$location   = app_acf_get_post_type_location( 'page' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'          => _app_page_group_key( 'meta_description' ),
			'title'        => 'Meta Description',
			'fields'       => array(
				array(
					'key'       => _app_page_field_key( 'meta_description' ),
					'name'      => 'meta_description',
					'label'     => '',
					'type'      => 'textarea',
					'maxlength' => '155',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'position'     => 'acf_after_title',
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_register_fields' );

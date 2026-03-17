<?php

function _app_page_installations_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-installations' ), ...$slugs );
}

function _app_page_installations_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-installations' ), ...$slugs );
}

function _app_page_installations_register_fields() {
	$location   = app_acf_get_page_template_location( 'page-installations' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'          => _app_page_installations_group_key( 'groups' ),
			'title'        => 'Installations',
			'fields'       => array(
				array(
					'key'          => _app_page_installations_field_key( 'groups' ),
					'label'        => 'Groups',
					'name'         => 'groups',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Group',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_installations_field_key( 'groups', 'title' ),
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_installations_field_key( 'groups', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'          => _app_page_installations_field_key( 'groups', 'items' ),
							'label'        => 'Installations',
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Installation',
							'sub_fields'   => array(
								array(
									'key'   => _app_page_installations_field_key( 'groups', 'items', 'name' ),
									'label' => 'Name',
									'name'  => 'name',
									'type'  => 'text',
								),
								array(
									'key'          => _app_page_installations_field_key( 'groups', 'items', 'image' ),
									'label'        => 'Image',
									'name'         => 'image',
									'type'         => 'image',
									'preview_size' => '3_2',
								),
								array(
									'key'          => _app_page_installations_field_key( 'groups', 'items', 'description' ),
									'label'        => 'Description',
									'name'         => 'description',
									'type'         => 'wysiwyg',
									'media_upload' => 0,
								),
							),
						),
					),
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		),
	);
}
add_action( 'acf/init', '_app_page_installations_register_fields' );

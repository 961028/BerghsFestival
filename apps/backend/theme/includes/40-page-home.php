<?php

function _app_page_home_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-home' ), ...$slugs );
}

function _app_page_home_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-home' ), ...$slugs );
}

function _app_page_home_register_options() {
	$location   = app_acf_get_page_template_location( 'page-home' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'            => _app_page_home_group_key( 'sections' ),
			'title'          => 'Content',
			'fields'         => array(
				array(
					'key'          => _app_page_home_field_key( 'sections' ),
					'label'        => '',
					'name'         => 'sections',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Section',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_home_field_key( 'sections', 'title' ),
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_home_field_key( 'sections', 'content' ),
							'label'        => 'Content',
							'name'         => 'content',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
					),
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_home_register_options' );

<?php

function _app_page_happenings_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-happenings' ), ...$slugs );
}

function _app_page_happenings_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-happenings' ), ...$slugs );
}

function _app_page_happenings_register_fields() {
	$location   = app_acf_get_page_template_location( 'page-happenings' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'        => _app_page_happenings_group_key( 'schedule' ),
			'title'      => 'Schedule',
			'fields'     => array(
				array(
					'key'          => _app_page_happenings_field_key( 'schedule' ),
					'label'        => '',
					'name'         => 'schedule',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Day',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_happenings_field_key( 'schedule', 'day' ),
							'label' => 'Day',
							'name'  => 'day',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_happenings_field_key( 'schedule', 'events' ),
							'label'        => 'Events',
							'name'         => 'events',
							'type'         => 'repeater',
							'button_label' => 'Add Event',
							'sub_fields'   => array(
								array(
									'key'   => _app_page_happenings_field_key( 'schedule', 'events', 'start_time' ),
									'label' => 'Starts',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'     => _app_page_happenings_field_key( 'schedule', 'events', 'title' ),
									'label'   => 'Title',
									'name'    => 'title',
									'type'    => 'text',
									'wrapper' => array(
										'width' => 80,
										'class' => '',
										'id'    => '',
									),
								),
							),
						),
					),
				),
			),
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
			'show_in_rest'=>true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'        => _app_page_happenings_group_key( 'groups' ),
			'title'      => 'Happenings',
			'fields'     => array(
				array(
					'key'          => _app_page_happenings_field_key( 'groups' ),
					'label'        => 'Groups',
					'name'         => 'groups',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Group',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_happenings_field_key( 'groups', 'title' ),
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_happenings_field_key( 'groups', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'          => _app_page_happenings_field_key( 'groups', 'items' ),
							'label'        => '',
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add Happening',
							'sub_fields'   => array(
								array(
									'key'   => _app_page_happenings_field_key( 'groups', 'items', 'name' ),
									'label' => 'Name',
									'name'  => 'name',
									'type'  => 'text',
								),
								array(
									'key'          => _app_page_happenings_field_key( 'groups', 'items', 'image' ),
									'label'        => 'Image',
									'name'         => 'image',
									'type'         => 'image',
									'preview_size' => '3_2',
								),
								array(
									'key'          => _app_page_happenings_field_key( 'groups', 'items', 'description' ),
									'label'        => 'Description',
									'name'         => 'description',
									'type'         => 'wysiwyg',
									'media_upload' => 0,
								),
								array(
									'key'   => _app_page_happenings_field_key( 'groups', 'items', 'url' ),
									'label' => 'URL',
									'name'  => 'url',
									'type'  => 'text',
								),
							),
						),
					),
				),
			),
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
			'show_in_rest'=>true,
		),
	);
}
add_action( 'acf/init', '_app_page_happenings_register_fields' );

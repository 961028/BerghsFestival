<?php

function _app_page_schedule_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-schedule' ), ...$slugs );
}

function _app_page_schedule_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-schedule' ), ...$slugs );
}

function _app_page_schedule_register_fields() {
	$location = app_acf_get_page_template_location( 'page-schedule' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_schedule_group_key( 'schedule' ),
			'title'        => 'Schedule',
			'fields'       => array(
				array(
					'key'          => _app_page_schedule_field_key( 'description' ),
					'label'        => 'Description',
					'name'         => 'description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				),
				array(
					'key'          => _app_page_schedule_field_key( 'schedule' ),
					'label'        => '',
					'name'         => 'schedule',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Day',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_schedule_field_key( 'schedule', 'day' ),
							'label' => 'Day',
							'name'  => 'day',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_schedule_field_key( 'schedule', 'events' ),
							'label'        => 'Events',
							'name'         => 'events',
							'type'         => 'repeater',
							'button_label' => 'Add Event',
							'sub_fields'   => array(
								array(
									'key'   => _app_page_schedule_field_key( 'schedule', 'events', 'start_time' ),
									'label' => 'Starts',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'   => _app_page_schedule_field_key( 'schedule', 'events', 'title' ),
									'label' => 'Title',
									'name'  => 'title',
									'type'  => 'text',
								),
								array(
									'key'   => _app_page_schedule_field_key( 'schedule', 'events', 'url' ),
									'label' => 'Link',
									'name'  => 'url',
									'type'  => 'url',
								),
							),
						),
					),
				),
			),
			'location'     => array( $location ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_schedule_register_fields' );

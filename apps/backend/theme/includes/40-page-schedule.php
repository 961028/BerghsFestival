<?php

function _app_page_schedule_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-schedule' ), ...$slugs );
}

function _app_page_schedule_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-schedule' ), ...$slugs );
}

function _app_page_schedule_register_fields() {
	$location              = app_acf_get_page_template_location( 'page-schedule' );
	$music_item_field_key  = _app_page_schedule_field_key( 'schedule', 'events', 'music_item' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_schedule_group_key( 'schedule' ),
			'title'        => 'Schedule',
			'fields'       => array(
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
									'key'           => $music_item_field_key,
									'label'         => 'Music item',
									'name'          => 'music_item',
									'type'          => 'post_object',
									'instructions'  => 'Optional. Link this event to a music item — title, time, and link will be inherited. Leave empty for non-music events (talks, breaks, etc.).',
									'post_type'     => array( 'music_item' ),
									'allow_null'    => 1,
									'return_format' => 'id',
									'ui'            => 1,
								),
								array(
									'key'               => _app_page_schedule_field_key( 'schedule', 'events', 'start_time' ),
									'label'             => 'Starts',
									'name'              => 'start_time',
									'type'              => 'text',
									'instructions'      => 'Used only when no music item is linked.',
									'conditional_logic' => array(
										array(
											array(
												'field'    => $music_item_field_key,
												'operator' => '==empty',
											),
										),
									),
								),
								array(
									'key'               => _app_page_schedule_field_key( 'schedule', 'events', 'title' ),
									'label'             => 'Title',
									'name'              => 'title',
									'type'              => 'text',
									'instructions'      => 'Used only when no music item is linked.',
									'conditional_logic' => array(
										array(
											array(
												'field'    => $music_item_field_key,
												'operator' => '==empty',
											),
										),
									),
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

<?php

function _app_page_experience_food_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-experience-food' ), ...$slugs );
}

function _app_page_experience_food_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-experience-food' ), ...$slugs );
}

function _app_page_experience_food_register_fields() {
	$location = app_acf_get_page_template_location( 'page-experience-food' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_experience_food_group_key( 'food' ),
			'title'        => 'Food & drink',
			'fields'       => array(
				array(
					'key'          => _app_page_experience_food_field_key( 'items' ),
					'label'        => '',
					'name'         => 'items',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Experience',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_experience_food_field_key( 'items', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => _app_page_experience_food_field_key( 'items', 'image' ),
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'preview_size'  => '3_2',
							'return_format' => 'id',
						),
						array(
							'key'          => _app_page_experience_food_field_key( 'items', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'   => _app_page_experience_food_field_key( 'items', 'url' ),
							'label' => 'Website URL',
							'name'  => 'url',
							'type'  => 'url',
						),
						array(
							'key'   => _app_page_experience_food_field_key( 'items', 'social_url' ),
							'label' => 'Social media URL',
							'name'  => 'social_url',
							'type'  => 'url',
						),
						array(
							'key'          => _app_page_experience_food_field_key( 'items', 'slots' ),
							'label'        => 'Slots',
							'name'         => 'slots',
							'type'         => 'repeater',
							'instructions' => 'One row per slot. Each slot has a day, start/end time, and location. A vendor can have multiple slots at the same day and time in different locations.',
							'layout'       => 'table',
							'button_label' => 'Add Slot',
							'min'          => 0,
							'sub_fields'   => array(
								array(
									'key'           => _app_page_experience_food_field_key( 'items', 'slots', 'day' ),
									'label'         => 'Day',
									'name'          => 'day',
									'type'          => 'select',
									'instructions'  => 'Pulled from the festival days defined on the Schedule page.',
									'choices'       => array(),
									'allow_null'    => 1,
									'return_format' => 'value',
								),
								array(
									'key'   => _app_page_experience_food_field_key( 'items', 'slots', 'start_time' ),
									'label' => 'Start',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'   => _app_page_experience_food_field_key( 'items', 'slots', 'end_time' ),
									'label' => 'End',
									'name'  => 'end_time',
									'type'  => 'text',
								),
								array(
									'key'           => _app_page_experience_food_field_key( 'items', 'slots', 'location' ),
									'label'         => 'Location',
									'name'          => 'location',
									'type'          => 'select',
									'instructions'  => 'Pulled from the locations defined on the Schedule page.',
									'choices'       => array(),
									'allow_null'    => 1,
									'return_format' => 'value',
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
add_action( 'acf/init', '_app_page_experience_food_register_fields' );

function _app_page_experience_food_load_slot_day_choices( $field ) {
	$schedule_page = app_acf_get_schedule_page();

	if ( ! $schedule_page ) {
		return $field;
	}

	$count = (int) get_post_meta( $schedule_page->ID, 'schedule', true );

	$choices = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$day = trim( (string) get_post_meta( $schedule_page->ID, "schedule_{$i}_day", true ) );

		if ( '' === $day ) {
			continue;
		}

		$choices[ $day ] = $day;
	}

	$field['choices'] = $choices;

	return $field;
}
add_filter(
	'acf/load_field/key=' . _app_page_experience_food_field_key( 'items', 'slots', 'day' ),
	'_app_page_experience_food_load_slot_day_choices'
);

add_filter(
	'acf/load_field/key=' . _app_page_experience_food_field_key( 'items', 'slots', 'location' ),
	'app_acf_load_schedule_location_choices'
);

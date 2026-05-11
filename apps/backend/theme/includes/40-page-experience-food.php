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
							'key'           => _app_page_experience_food_field_key( 'items', 'location' ),
							'label'         => 'Location',
							'name'          => 'location',
							'type'          => 'select',
							'instructions'  => 'Optional. Choices are pulled from the locations defined on the Schedule page.',
							'choices'       => array(),
							'allow_null'    => 1,
							'return_format' => 'value',
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
							'key'          => _app_page_experience_food_field_key( 'items', 'hours' ),
							'label'        => 'Opening hours',
							'name'         => 'hours',
							'type'         => 'repeater',
							'instructions' => 'Optional. One row per day with start/end time.',
							'layout'       => 'table',
							'button_label' => 'Add Opening Hours',
							'min'          => 0,
							'sub_fields'   => array(
								array(
									'key'           => _app_page_experience_food_field_key( 'items', 'hours', 'day' ),
									'label'         => 'Day',
									'name'          => 'day',
									'type'          => 'select',
									'instructions'  => 'Pulled from the festival days defined on the Schedule page.',
									'choices'       => array(),
									'allow_null'    => 1,
									'return_format' => 'value',
								),
								array(
									'key'   => _app_page_experience_food_field_key( 'items', 'hours', 'start_time' ),
									'label' => 'Start',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'   => _app_page_experience_food_field_key( 'items', 'hours', 'end_time' ),
									'label' => 'End',
									'name'  => 'end_time',
									'type'  => 'text',
								),
							),
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
					),
				),
			),
			'location'     => array( $location ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_experience_food_register_fields' );

add_filter(
	'acf/load_field/key=' . _app_page_experience_food_field_key( 'items', 'location' ),
	'app_acf_load_schedule_location_choices'
);

/**
 * Load schedule day choices for the food opening-hours `day` select.
 *
 * Mirrors `_app_page_experience_music_load_show_day_choices` but for food.
 */
function _app_page_experience_food_load_hours_day_choices( $field ) {
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
	'acf/load_field/key=' . _app_page_experience_food_field_key( 'items', 'hours', 'day' ),
	'_app_page_experience_food_load_hours_day_choices'
);

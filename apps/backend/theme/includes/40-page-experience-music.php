<?php

function _app_page_experience_music_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-experience-music' ), ...$slugs );
}

function _app_page_experience_music_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-experience-music' ), ...$slugs );
}

function _app_page_experience_music_register_fields() {
	$location = app_acf_get_page_template_location( 'page-experience-music' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_experience_music_group_key( 'music' ),
			'title'        => 'Music',
			'fields'       => array(
				array(
					'key'          => _app_page_experience_music_field_key( 'description' ),
					'label'        => 'Description',
					'name'         => 'description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				),
				array(
					'key'          => _app_page_experience_music_field_key( 'items' ),
					'label'        => '',
					'name'         => 'items',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Experience',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_experience_music_field_key( 'items', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => _app_page_experience_music_field_key( 'items', 'day' ),
							'label'         => 'Day',
							'name'          => 'day',
							'type'          => 'select',
							'instructions'  => 'Optional. Choices are pulled from the festival days defined on the Schedule page.',
							'choices'       => array(),
							'allow_null'    => 1,
							'return_format' => 'value',
							'wrapper'       => array(
								'width' => 30,
							),
						),
						array(
							'key'     => _app_page_experience_music_field_key( 'items', 'start_time' ),
							'label'   => 'Start time',
							'name'    => 'start_time',
							'type'    => 'text',
							'wrapper' => array(
								'width' => 30,
							),
						),
						array(
							'key'           => _app_page_experience_music_field_key( 'items', 'location' ),
							'label'         => 'Location',
							'name'          => 'location',
							'type'          => 'select',
							'instructions'  => 'Optional. Edit choices via Custom Fields → Field Groups.',
							'choices'       => array(),
							'allow_null'    => 1,
							'return_format' => 'value',
							'wrapper'       => array(
								'width' => 40,
							),
						),
						array(
							'key'           => _app_page_experience_music_field_key( 'items', 'image' ),
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'preview_size'  => '3_2',
							'return_format' => 'id',
						),
						array(
							'key'          => _app_page_experience_music_field_key( 'items', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'   => _app_page_experience_music_field_key( 'items', 'url' ),
							'label' => 'URL',
							'name'  => 'url',
							'type'  => 'text',
						),
					),
				),
			),
			'location'     => array( $location ),
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_page_experience_music_register_fields' );

function _app_page_experience_music_load_day_choices( $field ) {
	$schedule_page = get_page_by_path( 'schedule' );

	if ( ! $schedule_page ) {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'page-schedule.php',
				'number'     => 1,
			)
		);

		$schedule_page = $pages ? $pages[0] : null;
	}

	if ( ! $schedule_page ) {
		return $field;
	}

	$schedule = get_field( 'schedule', $schedule_page->ID );

	if ( ! is_array( $schedule ) ) {
		return $field;
	}

	$choices = array();

	foreach ( $schedule as $row ) {
		$day = isset( $row['day'] ) ? trim( (string) $row['day'] ) : '';

		if ( '' === $day ) {
			continue;
		}

		$choices[ $day ] = $day;
	}

	$field['choices'] = $choices;

	return $field;
}
add_filter(
	'acf/load_field/key=' . _app_page_experience_music_field_key( 'items', 'day' ),
	'_app_page_experience_music_load_day_choices'
);

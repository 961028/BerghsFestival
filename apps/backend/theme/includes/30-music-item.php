<?php

function _app_music_item_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_post_type_key_prefix( 'music_item' ), ...$slugs );
}

function _app_music_item_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_post_type_key_prefix( 'music_item' ), ...$slugs );
}

function _app_music_item_register_post_type() {
	register_post_type(
		'music_item',
		array(
			'labels'       => array(
				'name'          => 'Music Items',
				'singular_name' => 'Music Item',
			),
			'menu_icon'    => 'dashicons-format-audio',
			'public'       => false,
			'has_archive'  => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'rewrite'      => false,
			'supports'     => array(
				'title',
				'revisions',
				'page-attributes',
			),
			'show_in_rest' => true,
			'rest_base'    => 'music_item',
		),
	);
}
add_action( 'init', '_app_music_item_register_post_type' );

function _app_music_item_register_fields() {
	$location   = app_acf_get_post_type_location( 'music_item' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'          => _app_music_item_group_key( 'meta' ),
			'title'        => 'Details',
			'fields'       => array(
				array(
					'key'           => _app_music_item_field_key( 'day' ),
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
					'key'     => _app_music_item_field_key( 'start_time' ),
					'label'   => 'Start time',
					'name'    => 'start_time',
					'type'    => 'text',
					'wrapper' => array(
						'width' => 30,
					),
				),
				array(
					'key'           => _app_music_item_field_key( 'location' ),
					'label'         => 'Location',
					'name'          => 'location',
					'type'          => 'select',
					'instructions'  => 'Optional. Edit choices via Custom Fields → Field Groups.',
					'choices'       => array(
						'Ljusgården' => 'Ljusgården',
						'Aulan'      => 'Aulan',
						'Pink Room'  => 'Pink Room',
						'Gränden'    => 'Gränden',
						'Receptionen' => 'Receptionen',
					),
					'allow_null'    => 1,
					'return_format' => 'value',
					'wrapper'       => array(
						'width' => 40,
					),
				),
				array(
					'key'           => _app_music_item_field_key( 'image' ),
					'label'         => 'Image',
					'name'          => 'image',
					'type'          => 'image',
					'preview_size'  => '3_2',
					'return_format' => 'id',
				),
				array(
					'key'          => _app_music_item_field_key( 'description' ),
					'label'        => 'Description',
					'name'         => 'description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				),
				array(
					'key'   => _app_music_item_field_key( 'url' ),
					'label' => 'Website URL',
					'name'  => 'url',
					'type'  => 'url',
				),
				array(
					'key'   => _app_music_item_field_key( 'social_url' ),
					'label' => 'Social media URL',
					'name'  => 'social_url',
					'type'  => 'url',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		)
	);
}
add_action( 'acf/init', '_app_music_item_register_fields' );

function _app_music_item_load_day_choices( $field ) {
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
	'acf/load_field/key=' . _app_music_item_field_key( 'day' ),
	'_app_music_item_load_day_choices'
);

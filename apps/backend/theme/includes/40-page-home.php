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
			'key'            => _app_page_home_group_key( 'hero' ),
			'title'          => 'Hero',
			'fields'         => array(
				array(
					'key'        => _app_page_home_field_key( 'hero', 'video' ),
					'label'      => 'Video',
					'name'       => 'hero_video',
					'type'       => 'file',
					'mime_types' => 'mp4',
				),
				array(
					'key'           => _app_page_home_field_key( 'hero', 'image' ),
					'label'         => 'Fallback Image',
					'name'          => 'hero_image',
					'type'          => 'image',
					'return_format' => 'id',
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest'   => true,
		)
	);

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
			'show_in_rest'   => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'            => _app_page_home_group_key( 'festival' ),
			'title'          => 'Festival',
			'fields'         => array(
				array(
					'key'   => _app_page_home_field_key( 'registration_url' ),
					'label' => 'Registration URL',
					'name'  => 'registration_url',
					'type'  => 'text',
				),
				array(
					'key'            => _app_page_home_field_key( 'opening_date' ),
					'label'          => 'Opening Date & Time',
					'name'           => 'opening_date',
					'type'           => 'date_time_picker',
					'display_format' => 'Y-m-d H:i',
					'return_format'  => 'Y-m-d\TH:i:s',
				),
				array(
					'key'   => _app_page_home_field_key( 'venue' ),
					'label' => 'Venue',
					'name'  => 'venue',
					'type'  => 'text',
				),
				array(
					'key'   => _app_page_home_field_key( 'student_count' ),
					'label' => 'Number of Students',
					'name'  => 'student_count',
					'type'  => 'number',
				),
				array(
					'key'   => _app_page_home_field_key( 'project_count' ),
					'label' => 'Number of Projects',
					'name'  => 'project_count',
					'type'  => 'number',
				),
				array(
					'key'          => _app_page_home_field_key( 'festival_days' ),
					'label'        => '',
					'name'         => 'festival_days',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Day',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_home_field_key( 'festival_days', 'abbr' ),
							'label' => 'Abbreviation',
							'name'  => 'abbr',
							'type'  => 'text',
						),
						array(
							'key'   => _app_page_home_field_key( 'festival_days', 'date' ),
							'label' => 'Date',
							'name'  => 'date',
							'type'  => 'text',
						),
						array(
							'key'   => _app_page_home_field_key( 'festival_days', 'hours' ),
							'label' => 'Hours',
							'name'  => 'hours',
							'type'  => 'text',
						),
					),
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest'   => true,
		)
	);
}
add_action( 'acf/init', '_app_page_home_register_options' );

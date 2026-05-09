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
					'key'          => _app_page_schedule_field_key( 'locations' ),
					'label'        => 'Locations',
					'name'         => 'locations',
					'type'         => 'repeater',
					'instructions' => 'Locations available across the festival (e.g. Ljusgården, Aulan). Used as the choices for Location fields on Music artists, Installations, and Food & drink.',
					'layout'       => 'table',
					'button_label' => 'Add Location',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_schedule_field_key( 'locations', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
					),
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
									'key'           => _app_page_schedule_field_key( 'schedule', 'events', 'artist' ),
									'label'         => 'Artist',
									'name'          => 'artist',
									'type'          => 'select',
									'instructions'  => 'Optional. Choices come from the artists defined on the Music page. Leave empty for non-music events (talks, breaks, etc.).',
									'choices'       => array(),
									'allow_null'    => 1,
									'return_format' => 'value',
								),
								array(
									'key'   => _app_page_schedule_field_key( 'schedule', 'events', 'start_time' ),
									'label' => 'Starts',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'               => _app_page_schedule_field_key( 'schedule', 'events', 'title' ),
									'label'             => 'Title',
									'name'              => 'title',
									'type'              => 'text',
									'instructions'      => 'Used only when no artist is linked.',
									'conditional_logic' => array(
										array(
											array(
												'field'    => _app_page_schedule_field_key( 'schedule', 'events', 'artist' ),
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

add_filter(
	'acf/load_field/key=' . _app_page_schedule_field_key( 'schedule', 'events', 'artist' ),
	'app_acf_load_music_page_artist_choices'
);

function app_acf_get_schedule_page(): ?WP_Post {
	$schedule_page = get_page_by_path( 'schedule' );

	if ( $schedule_page ) {
		return $schedule_page;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-schedule.php',
			'number'     => 1,
		)
	);

	return $pages ? $pages[0] : null;
}

function app_acf_get_schedule_location_choices(): array {
	$schedule_page = app_acf_get_schedule_page();

	if ( ! $schedule_page ) {
		return array();
	}

	$count = (int) get_post_meta( $schedule_page->ID, 'locations', true );

	$choices = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$name = trim( (string) get_post_meta( $schedule_page->ID, "locations_{$i}_name", true ) );

		if ( '' === $name ) {
			continue;
		}

		$choices[ $name ] = $name;
	}

	return $choices;
}

function app_acf_load_schedule_location_choices( $field ) {
	$field['choices'] = app_acf_get_schedule_location_choices();

	return $field;
}

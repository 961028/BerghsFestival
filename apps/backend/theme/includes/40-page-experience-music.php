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
					'key'          => _app_page_experience_music_field_key( 'artists' ),
					'label'        => 'Artists',
					'name'         => 'artists',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Artist',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_experience_music_field_key( 'artists', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'          => _app_page_experience_music_field_key( 'artists', 'slug' ),
							'label'        => 'Slug',
							'name'         => 'slug',
							'type'         => 'text',
							'instructions' => 'URL-safe identifier for anchor links. Auto-generated from Name on save — only override to preserve schedule links after renaming an artist.',
						),
						array(
							'key'           => _app_page_experience_music_field_key( 'artists', 'image' ),
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'preview_size'  => '3_2',
							'return_format' => 'id',
						),
						array(
							'key'          => _app_page_experience_music_field_key( 'artists', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'   => _app_page_experience_music_field_key( 'artists', 'url' ),
							'label' => 'Website URL',
							'name'  => 'url',
							'type'  => 'url',
						),
						array(
							'key'   => _app_page_experience_music_field_key( 'artists', 'social_url' ),
							'label' => 'Social media URL',
							'name'  => 'social_url',
							'type'  => 'url',
						),
						array(
							'key'          => _app_page_experience_music_field_key( 'artists', 'slots' ),
							'label'        => 'Slots',
							'name'         => 'slots',
							'type'         => 'repeater',
							'instructions' => 'One row per slot. Each slot has a day, start/end time, and location.',
							'layout'       => 'table',
							'button_label' => 'Add Slot',
							'min'          => 1,
							'sub_fields'   => array(
								array(
									'key'           => _app_page_experience_music_field_key( 'artists', 'slots', 'day' ),
									'label'         => 'Day',
									'name'          => 'day',
									'type'          => 'select',
									'instructions'  => 'Pulled from the festival days defined on the Schedule page.',
									'choices'       => array(),
									'allow_null'    => 1,
									'return_format' => 'value',
								),
								array(
									'key'   => _app_page_experience_music_field_key( 'artists', 'slots', 'start_time' ),
									'label' => 'Start',
									'name'  => 'start_time',
									'type'  => 'text',
								),
								array(
									'key'   => _app_page_experience_music_field_key( 'artists', 'slots', 'end_time' ),
									'label' => 'End',
									'name'  => 'end_time',
									'type'  => 'text',
								),
								array(
									'key'           => _app_page_experience_music_field_key( 'artists', 'slots', 'location' ),
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
add_action( 'acf/init', '_app_page_experience_music_register_fields' );

function _app_page_experience_music_load_slot_day_choices( $field ) {
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
	'acf/load_field/key=' . _app_page_experience_music_field_key( 'artists', 'slots', 'day' ),
	'_app_page_experience_music_load_slot_day_choices'
);

add_filter(
	'acf/load_field/key=' . _app_page_experience_music_field_key( 'artists', 'slots', 'location' ),
	'app_acf_load_schedule_location_choices'
);

/**
 * Get artists from the music page as a list of associative rows keyed by slug.
 *
 * Returns array<string, array{ name: string }>.
 */
function app_acf_get_music_page_artist_choices(): array {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'page-experience-music.php',
			'number'     => 1,
		)
	);

	if ( empty( $pages ) ) {
		return array();
	}

	$count = (int) get_post_meta( $pages[0]->ID, 'artists', true );

	$choices = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$name = trim( (string) get_post_meta( $pages[0]->ID, "artists_{$i}_name", true ) );

		if ( '' === $name ) {
			continue;
		}

		// Prefer stored slug so schedule links survive artist renames.
		$slug = trim( (string) get_post_meta( $pages[0]->ID, "artists_{$i}_slug", true ) );
		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		if ( '' === $slug ) {
			continue;
		}

		$choices[ $slug ] = $name;
	}

	return $choices;
}

function _app_page_experience_music_autofill_slugs( int $post_id ): void {
	$template = get_post_meta( $post_id, '_wp_page_template', true );

	if ( 'page-experience-music.php' !== $template ) {
		return;
	}

	$count = (int) get_post_meta( $post_id, 'artists', true );

	for ( $i = 0; $i < $count; $i++ ) {
		$existing_slug = trim( (string) get_post_meta( $post_id, "artists_{$i}_slug", true ) );

		if ( '' !== $existing_slug ) {
			continue;
		}

		$name = trim( (string) get_post_meta( $post_id, "artists_{$i}_name", true ) );

		if ( '' === $name ) {
			continue;
		}

		update_post_meta( $post_id, "artists_{$i}_slug", sanitize_title( $name ) );
	}
}
add_action( 'acf/save_post', '_app_page_experience_music_autofill_slugs', 20 );

function app_acf_load_music_page_artist_choices( $field ) {
	$field['choices'] = app_acf_get_music_page_artist_choices();

	return $field;
}

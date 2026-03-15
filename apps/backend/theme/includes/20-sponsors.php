<?php

function _app_sponsors_register_options() {
	acf_add_options_page(
		array(
			'page_title' => 'Sponsor Options',
			'menu_title' => 'Sponsors',
			'menu_slug'  => 'app-sponsors',
			'icon_url'   => 'dashicons-money-alt',
			'position'   => '30',
			'capability' => 'manage_options',
		)
	);

	$key      = app_acf_get_options_key( 'sponsors' );
	$location = app_acf_get_options_page_location( 'app-sponsors' );

	acf_add_local_field_group(
		array(
			'key'          => "group-$key",
			'title'        => 'Sponsors',
			'fields'       => array(
				array(
					'key'          => "$key-sponsors",
					'label'        => '',
					'name'         => 'sponsors',
					'type'         => 'repeater',
					'button_label' => 'Add Sponsor',
					'sub_fields'   => array(
						array(
							'key'   => "$key-sponsors-name",
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => "$key-sponsors-image",
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'id',
						),
						array(
							'key'   => "$key-sponsors-url",
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
add_action( 'acf/init', '_app_sponsors_register_options' );

function _app_sponsors_on_rest_api_init() {
	register_rest_route(
		'app/v1',
		'sponsors',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => '_app_sponsors_rest_api_callback',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', '_app_sponsors_on_rest_api_init' );

function _app_sponsors_rest_api_callback(): WP_REST_Response {
	$rows = get_field( 'sponsors', 'options' );

	if ( ! is_array( $rows ) ) {
		$rows = array();
	}

	$items = [];

	$id = 1;

	foreach ($rows as $row) {
		$name = $row['name'] ?? null;
		if (!is_string($name) || $name === '') {
			continue;
		}

		$imageId = $row['image'] ?? null;
		if (!is_int($imageId) || $imageId <=0) {
			continue;
		}

		$url = $row['url'] ?? null;
		if (!is_string($url) || $url === '') {
			continue;
		}

		$items[] = [
			'id'    => $id++,
			'name'  => $name,
			'image' => $imageId,
			'url'   => $url,
		];
	}

	return rest_ensure_response( $items );
}

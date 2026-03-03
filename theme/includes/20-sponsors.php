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
			'key'      => "group-$key",
			'title'    => 'Sponsors',
			'fields'   => array(
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
							'key'   => "$key-sponsors-image",
							'label' => 'Image',
							'name'  => 'image',
							'type'  => 'image',
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
			'location' => array( $location ),
		)
	);
}
add_action( 'acf/init', '_app_sponsors_register_options' );

function app_sponsors_get(): array {
	$value = get_field( 'sponsors', 'options' );

	if ( ! is_array( $value ) ) {
		return array();
	}

	return $value;
}

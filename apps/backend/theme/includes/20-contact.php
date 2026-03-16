<?php

function _app_contact_get_social_service_slug_to_label(): array {
	return array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'linkedin'  => 'Linkedin',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
	);
}

function _app_contact_register_options() {
	acf_add_options_page(
		array(
			'page_title' => 'Contact Options',
			'menu_title' => 'Contact',
			'menu_slug'  => 'app-contact',
			'icon_url'   => 'dashicons-id',
			'position'   => '30',
			'capability' => 'manage_options',
		)
	);

	$key        = app_acf_get_options_key( 'contact' );
	$location   = app_acf_get_options_page_location( 'app-contact' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'        => "group-$key-address_phone",
			'title'      => 'Contact',
			'fields'     => array(
				array(
					'key'   => "$key-address",
					'name'  => 'contact-address',
					'label' => 'Address',
					'type'  => 'textarea',
				),
				array(
					'key'   => "$key-phone",
					'name'  => 'contact-phone',
					'label' => 'Phone',
					'type'  => 'text',
				),
			),
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
		)
	);

	$social_fields = array();

	foreach ( _app_contact_get_social_service_slug_to_label() as $service_slug => $service_label ) {
		$social_fields[] = array(
			'key'   => "$key-{$service_slug}_url",
			'name'  => "contact-{$service_slug}_url",
			'label' => "{$service_label} URL",
			'type'  => 'text',
		);
	}
	acf_add_local_field_group(
		array(
			'key'        => "group-$key-social",
			'title'      => 'Social Media',
			'fields'     => $social_fields,
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
		)
	);
}
add_action( 'acf/init', '_app_contact_register_options' );

function _app_contact_get( string $name ): string {
	$value = get_field( 'contact-' . $name, 'options' );

	if ( ! is_string( $value ) ) {
		return '';
	}

	return $value;
}

/**
 * Get the contact social media services.
 *
 * @return list<array{icon:string,label:string,url:string}>
 */
function _app_contact_get_social_services(): array {
	$slug_to_label = _app_contact_get_social_service_slug_to_label();

	$value = array();

	foreach ( $slug_to_label as $slug => $label ) {
		$url = get_field( "contact-{$slug}_url", 'options' );

		if ( ! is_string( $url ) || '' === $url ) {
			continue;
		}

		$value[] = array(
			'icon'  => "social/$slug",
			'label' => $label,
			'url'   => $url,
		);
	}

	return $value;
}

function _app_contact_on_rest_api_init() {
	register_rest_route(
		'app/v1',
		'contact',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => '_app_contact_rest_api_callback',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', '_app_contact_on_rest_api_init' );

function _app_contact_rest_api_callback(): WP_REST_Response {
	return rest_ensure_response(
		array(
			'address'         => _app_contact_get( 'address' ),
			'phone'           => _app_contact_get( 'phone' ),
			'social_services' => _app_contact_get_social_services(),
		)
	);
}

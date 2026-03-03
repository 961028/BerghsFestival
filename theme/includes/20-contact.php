<?php

function _app_contact_get_social_services(): array {
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
			'key'        => "group-$key",
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

	foreach ( _app_contact_get_social_services() as $service_slug => $service_label ) {
		$social_fields[] = array(
			'key'   => "$key-{$service_slug}_url",
			'name'  => "contact-{$service_slug}_url",
			'label' => "{$service_label} URL",
			'type'  => 'text',
		);
	}
	acf_add_local_field_group(
		array(
			'key'        => "group-$key",
			'title'      => 'Social Media',
			'fields'     => $social_fields,
			'location'   => array( $location ),
			'menu_order' => $menu_order++,
		)
	);
}
add_action( 'acf/init', '_app_contact_register_options' );

function _app_contact_get( string $name ): ?string {
	$value = get_field( 'contact_' . $name, 'options' );

	if ( ! is_string( $value ) || '' === $value ) {
		return null;
	}

	return $value;
}

function app_contact_get_address(): ?string {
	return _app_contact_get( 'address' );
}

function app_contact_get_phone(): ?string {
	return _app_contact_get( 'phone' );
}

/**
 * Get the contact social media services.
 *
 * @return list<array{icon:string,label:string,url:string}>
 */
function app_contact_get_social_services(): array {
	$slug_to_label = array_keys( _app_contact_get_social_services() );

	$value = array();

	foreach ( $slug_to_label as $slug => $label ) {
		$url = get_field( "contact-{$slug}_url" );

		if ( ! is_string( $url ) || '' === $url ) {
			continue;
		}

		$value[] = array(
			'icon'  => $slug,
			'label' => $label,
			'url'   => $url,
		);
	}

	return $value;
}

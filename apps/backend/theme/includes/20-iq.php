<?php

function _app_iq_register_options() {
    acf_add_options_page([
        'page_title' => 'IQ',
        'menu_title' => 'IQ',
        'menu_slug'  => 'app-iq',
        'icon_url'   => 'dashicons-beer',
        'position'   => '30',
        'capability' => 'manage_options',
    ]);

	$key      = app_acf_get_options_key( 'iq' );
	$location = app_acf_get_options_page_location( 'app-iq' );
    $menuOrder = 0;

    acf_add_local_field_group([
        'key'      => 'group-' . $key,
        'title'    => 'Content',
        'fields'   => [
            [
                'key' => $key . '-title',
                'label' => 'Title',
                'name' => 'iq-title',
                'type' => 'text',
            ],
            [
                'key'   => $key . '-content',
                'label' => '',
                'name'  => 'iq-content',
                'type'  => 'wysiwyg',
                'toolbar'      => 'basic',
                'media_upload' => false,

            ],
        ],
        'location' => [ $location ],
        'menu_order' => $menuOrder++,
    ]);
}
add_action( 'acf/init', '_app_iq_register_options' );

function _app_iq_on_rest_api_init() {
	register_rest_route(
		'app/v1',
		'iq',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => '_app_iq_rest_api_callback',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', '_app_iq_on_rest_api_init' );


function _app_iq_get( string $name ): string {
	$value = get_field( 'iq-' . $name, 'options' );

	if ( ! is_string( $value )) {
		return '';
	}

	return $value;
}

function _app_iq_rest_api_callback(): WP_REST_Response {
	return rest_ensure_response( [
        'title'   => _app_iq_get('title'),
        'content' => _app_iq_get('content'),
    ]);
}

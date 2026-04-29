<?php

function _app_page_experience_food_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-experience-food' ), ...$slugs );
}

function _app_page_experience_food_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-experience-food' ), ...$slugs );
}

function _app_page_experience_food_register_fields() {
	$location = app_acf_get_page_template_location( 'page-experience-food' );

	acf_add_local_field_group(
		array(
			'key'          => _app_page_experience_food_group_key( 'food' ),
			'title'        => 'Food & drink',
			'fields'       => array(
				array(
					'key'          => _app_page_experience_food_field_key( 'description' ),
					'label'        => 'Description',
					'name'         => 'description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				),
				array(
					'key'          => _app_page_experience_food_field_key( 'items' ),
					'label'        => '',
					'name'         => 'items',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Experience',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_experience_food_field_key( 'items', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => _app_page_experience_food_field_key( 'items', 'location' ),
							'label'         => 'Location',
							'name'          => 'location',
							'type'          => 'select',
							'instructions'  => 'Optional. Edit choices via Custom Fields → Field Groups.',
							'choices'       => array(),
							'allow_null'    => 1,
							'return_format' => 'value',
						),
						array(
							'key'           => _app_page_experience_food_field_key( 'items', 'image' ),
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'preview_size'  => '3_2',
							'return_format' => 'id',
						),
						array(
							'key'          => _app_page_experience_food_field_key( 'items', 'description' ),
							'label'        => 'Description',
							'name'         => 'description',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
						),
						array(
							'key'   => _app_page_experience_food_field_key( 'items', 'url' ),
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
add_action( 'acf/init', '_app_page_experience_food_register_fields' );

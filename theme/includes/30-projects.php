<?php

function _app_project_register_post_type() {
	register_post_type(
		'project',
		array(
			'labels'      => array(
				'name'          => __( 'Projects', 'app' ),
				'singular_name' => __( 'Project', 'app' ),
			),
			'menu_icon'   => 'dashicons-portfolio',
			'public'      => true,
			'has_archive' => false,
			'rewrite'     => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
			'supports'    => array(
				'title',
				'editor',
				'revisions',
			),
		),
	);
}
add_action( 'init', '_app_project_register_post_type' );

function _app_project_register_fields() {
	$key        = app_acf_get_post_type_key( 'project' );
	$location   = app_acf_get_post_type_location( 'project' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'        => "group-$key-image",
			'title'      => 'Image',
			'fields'     => array(
				array(
					'key'          => "$key-image",
					'label'        => '',
					'name'         => 'image',
					'type'         => 'image',
					'preview_size' => '3_2',
				),
			),
			'location'   => array( $location ),
			'position'   => 'acf_after_title',
			'menu_order' => $menu_order++,
		),
	);

	acf_add_local_field_group(
		array(
			'key'        => "group-$key-team_members",
			'title'      => 'Team Members',
			'fields'     => array(
				array(
					'key'          => "$key-team_members",
					'label'        => '',
					'name'         => 'team_members',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Member',
					'sub_fields'   => array(
						array(
							'key'   => "$key-team_members-name",
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'     => "$key-team_members-class",
							'label'   => 'Class',
							'name'    => 'class',
							'type'    => 'select',
							'choices' => array(
								''                         => '',
								'Public Relations'         => 'Public Relations',
								'Strategisk kommunikation' => 'Strategisk kommunikation',
								'Digital Design & Strategy' => 'Digital Design & Strategy',
								'Growth Marketing'         => 'Growth Marketing',
								'Produktionsledning'       => 'Produktionsledning',
								'Art Director'             => 'Art Director',
								'Copywriter'               => 'Copywriter',
								'Communication Design'     => 'Communication Design',
								'Handledare'               => 'Handledare',
							),
						),
					),
				),
			),
			'location'   => array( $location ),
			'position'   => 'acf_after_title',
			'menu_order' => $menu_order++,
		),
	);

	acf_add_local_field_group(
		array(
			'key'        => "group-$key-preamble",
			'title'      => 'Preamble',
			'fields'     => array(
				array(
					'key'          => "$key-preamble",
					'label'        => '',
					'name'         => 'preamble',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				),
			),
			'location'   => array( $location ),
			'position'   => 'acf_after_title',
			'menu_order' => $menu_order++,
		),
	);

	acf_add_local_field_group(
		array(
			'key'        => "group-$key-video",
			'title'      => 'Video',
			'fields'     => array(
				array(
					'key'   => "$key-video",
					'name'  => 'video',
					'label' => '',
					'type'  => 'oembed',
				),
			),
			'location'   => array( $location ),
			'position'   => 'acf_after_title',
			'menu_order' => $menu_order++,
		)
	);
}
add_action( 'acf/init', '_app_project_register_fields' );

function _app_project_filter_wpseo_add_opengraph_images( $image_container ) {
	if ( ! is_singular( 'project' ) ) {
		return;
	}

	$image = get_field( 'image' );

	if ( ! $image ) {
		return;
	}

	$image_container->add_image_by_id( $image['id'] );
}
add_action( 'wpseo_add_opengraph_images', '_app_project_filter_wpseo_add_opengraph_images' );

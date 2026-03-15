<?php

function _app_project_register_post_type() {
	register_post_type(
		'project',
		array(
			'labels'       => array(
				'name'          => 'Projects',
				'singular_name' => 'Project',
			),
			'menu_icon'    => 'dashicons-portfolio',
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
			'supports'     => array(
				'title',
				'revisions',
			),
			'show_in_rest' => true,
			'rest_base'    => 'projects',
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
			'key'          => "group-$key-company",
			'title'        => 'Company',
			'fields'       => array(
				array(
					'key'   => "$key-company",
					'name'  => 'company',
					'label' => '',
					'type'  => 'text',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'          => "group-$key-image",
			'title'        => 'Image',
			'fields'       => array(
				array(
					'key'           => "$key-image",
					'label'         => '',
					'name'          => 'image',
					'type'          => 'image',
					'preview_size'  => '3_2',
					'return_format' => 'id',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		),
	);

	acf_add_local_field_group(
		array(
			'key'          => "group-$key-video",
			'title'        => 'Video',
			'fields'       => array(
				array(
					'key'   => "$key-video",
					'name'  => 'video',
					'label' => '',
					'type'  => 'oembed',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'          => "group-$key-team_members",
			'title'        => 'Team Members',
			'fields'       => array(
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
								''      => '',
								'AD'    => 'Art Director',
								'CD'    => 'Communication Design',
								'CE'    => 'Content Engineering',
								'CW'    => 'Copywriter',
								'DDS'   => 'Digital Design & Strategy',
								'GM'    => 'Growth Marketing',
								'PL'    => 'Produktionsledning',
								'PR'    => 'Public Relations',
								'SK'    => 'Strategisk kommunikation',
								'Tutor' => 'Handledare',
							),
						),
					),
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		),
	);

	acf_add_local_field_group(
		array(
			'key'          => "group-$key-content",
			'title'        => 'Innehåll',
			'fields'       => array(
				array(
					'key'          => $key . '-content-company',
					'label'        => 'The Company',
					'name'         => 'content-company',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'          => $key . '-content-background',
					'label'        => 'Background',
					'name'         => 'content-background',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'          => $key . '-content-solution',
					'label'        => 'Solution',
					'name'         => 'content-solution',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),

			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'show_in_rest' => true,
		),
	);
}
add_action( 'acf/init', '_app_project_register_fields' );

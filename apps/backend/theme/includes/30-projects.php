<?php

function _app_projects_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_post_type_key_prefix( 'projects' ), ...$slugs );
}

function _app_projects_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_post_type_key_prefix( 'projects' ), ...$slugs );
}

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
			'has_archive'  => true,
			'show_in_menu' => true,
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
	$location   = app_acf_get_post_type_location( 'project' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'          => _app_projects_group_key( 'meta_description' ),
			'title'        => 'Meta Description',
			'fields'       => array(
				array(
					'key'       => _app_projects_field_key( 'meta_description' ),
					'name'      => 'meta_description',
					'label'     => '',
					'type'      => 'textarea',
					'maxlength' => '155',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'position'     => 'acf_after_title',
			'show_in_rest' => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'          => _app_projects_group_key( 'project_type' ),
			'title'        => 'Type',
			'fields'       => array(
				array(
					'key'           => _app_projects_field_key( 'project_type' ),
					'name'          => 'project_type',
					'label'         => '',
					'type'          => 'select',
					'choices'       => array(
						'group'      => 'Group',
						'individual' => 'Individual',
					),
					'default_value' => 'group',
				),
			),
			'location'     => array( $location ),
			'menu_order'   => $menu_order++,
			'position'     => 'side',
			'show_in_rest' => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'          => _app_projects_group_key( 'company' ),
			'title'        => 'Company',
			'fields'       => array(
				array(
					'key'   => _app_projects_field_key( 'company' ),
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
			'key'          => _app_projects_group_key( 'image' ),
			'title'        => 'Image',
			'fields'       => array(
				array(
					'key'           => _app_projects_field_key( 'image' ),
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
			'key'          => _app_projects_group_key( 'video' ),
			'title'        => 'Video',
			'fields'       => array(
				array(
					'key'   => _app_projects_field_key( 'video' ),
					'name'  => 'video',
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
			'key'          => _app_projects_group_key( 'team_members' ),
			'title'        => 'Team Members',
			'fields'       => array(
				array(
					'key'          => _app_projects_field_key( 'team_members' ),
					'label'        => '',
					'name'         => 'team_members',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Member',
					'sub_fields'   => array(
						array(
							'key'   => _app_projects_field_key( 'team_members', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
						array(
							'key'           => _app_projects_field_key( 'team_members', 'class' ),
							'label'         => 'Class',
							'name'          => 'class',
							'type'          => 'select',
							'return_format' => 'label',
							'choices'       => array(
								''      => '',
								'AD'    => 'Art Direction',
								'CD'    => 'Communication Design',
								'CE'    => 'AI Content Engineering',
								'CW'    => 'Copywriting',
								'DDS'   => 'Digital Design & Strategy',
								'GM'    => 'Growth Marketing',
								'PL'    => 'Production Management',
								'PR'    => 'Public Relations',
								'SK'    => 'Strategic Communication',
								'Tutor' => 'Tutor',
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
			'key'          => _app_projects_group_key( 'content' ),
			'title'        => 'Innehåll',
			'fields'       => array(
				array(
					'key'          => _app_projects_field_key( 'content', 'company' ),
					'label'        => 'The Company',
					'name'         => 'content-company',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'          => _app_projects_field_key( 'content', 'background' ),
					'label'        => 'Background',
					'name'         => 'content-background',
					'type'         => 'wysiwyg',
					'toolbar'      => 'basic',
					'media_upload' => false,
				),
				array(
					'key'          => _app_projects_field_key( 'content', 'solution' ),
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

function _app_project_resolve_team_member_class_labels( $response, $post ) {
	if ( $post->post_type !== 'project' ) {
		return $response;
	}

	$data = $response->get_data();

	if ( empty( $data['acf']['team_members'] ) || ! is_array( $data['acf']['team_members'] ) ) {
		return $response;
	}

	$field   = get_field_object( _app_projects_field_key( 'team_members', 'class' ) );
	$choices = $field['choices'] ?? array();

	foreach ( $data['acf']['team_members'] as &$member ) {
		if ( isset( $member['class'], $choices[ $member['class'] ] ) ) {
			$member['class'] = $choices[ $member['class'] ];
		}
	}
	unset( $member );

	$response->set_data( $data );

	return $response;
}
add_filter( 'rest_prepare_project', '_app_project_resolve_team_member_class_labels', 10, 2 );

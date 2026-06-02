<?php

function _app_page_aftermovie_group_key( string ...$slugs ): string {
	return app_acf_get_group_key( app_acf_get_page_template_key_prefix( 'page-aftermovie' ), ...$slugs );
}

function _app_page_aftermovie_field_key( string ...$slugs ): string {
	return app_acf_get_field_key( app_acf_get_page_template_key_prefix( 'page-aftermovie' ), ...$slugs );
}

function _app_page_aftermovie_register_fields() {
	$location   = app_acf_get_page_template_location( 'page-aftermovie' );
	$menu_order = 0;

	acf_add_local_field_group(
		array(
			'key'            => _app_page_aftermovie_group_key( 'media' ),
			'title'          => 'Media',
			'fields'         => array(
				array(
					'key'          => _app_page_aftermovie_field_key( 'media', 'heading' ),
					'label'        => 'Heading',
					'name'         => 'heading',
					'type'         => 'text',
					'instructions' => 'Override the page h1. Leave blank to use the page title.',
				),
				array(
					'key'           => _app_page_aftermovie_field_key( 'media', 'video_url' ),
					'label'         => 'Video URL',
					'name'          => 'video_url',
					'type'          => 'file',
					'instructions'  => 'Aftermovie video file (MP4, etc). The page\'s featured image is used as the video poster, or as the hero image when no video is set.',
					'return_format' => 'url',
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest'   => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'            => _app_page_aftermovie_group_key( 'content' ),
			'title'          => 'Content',
			'fields'         => array(
				array(
					'key'          => _app_page_aftermovie_field_key( 'content', 'blocks' ),
					'label'        => '',
					'name'         => 'blocks',
					'type'         => 'flexible_content',
					'button_label' => 'Add Block',
					'layouts'      => array(
						array(
							'key'        => _app_page_aftermovie_field_key( 'content', 'blocks', 'paragraph' ),
							'name'       => 'paragraph',
							'label'      => 'Paragraph',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'          => _app_page_aftermovie_field_key( 'content', 'blocks', 'paragraph', 'text' ),
									'label'        => 'Text',
									'name'         => 'text',
									'type'         => 'wysiwyg',
									'media_upload' => 0,
								),
							),
						),
						array(
							'key'        => _app_page_aftermovie_field_key( 'content', 'blocks', 'image' ),
							'name'       => 'image',
							'label'      => 'Image',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => _app_page_aftermovie_field_key( 'content', 'blocks', 'image', 'image' ),
									'label'         => 'Image',
									'name'          => 'image',
									'type'          => 'image',
									'return_format' => 'id',
									'preview_size'  => 'medium',
								),
								array(
									'key'   => _app_page_aftermovie_field_key( 'content', 'blocks', 'image', 'caption' ),
									'label' => 'Caption',
									'name'  => 'caption',
									'type'  => 'text',
								),
							),
						),
					),
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest'   => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'            => _app_page_aftermovie_group_key( 'contributors' ),
			'title'          => 'Contributors',
			'fields'         => array(
				array(
					'key'          => _app_page_aftermovie_field_key( 'contributors' ),
					'label'        => '',
					'name'         => 'contributors',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add Contributor',
					'sub_fields'   => array(
						array(
							'key'   => _app_page_aftermovie_field_key( 'contributors', 'role' ),
							'label' => 'Role',
							'name'  => 'role',
							'type'  => 'text',
						),
						array(
							'key'   => _app_page_aftermovie_field_key( 'contributors', 'name' ),
							'label' => 'Name',
							'name'  => 'name',
							'type'  => 'text',
						),
					),
				),
			),
			'location'       => array( $location ),
			'hide_on_screen' => array(
				'the_content',
			),
			'menu_order'     => $menu_order++,
			'show_in_rest'   => true,
		)
	);
}
add_action( 'acf/init', '_app_page_aftermovie_register_fields' );

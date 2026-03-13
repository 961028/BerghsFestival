<?php

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

function _app_seed_command(): void {
	$faker = Faker\Factory::create();

	WP_CLI::line( 'Seeding...' );

	// Delete existing posts

	foreach ( array( 'post', 'page', 'project', 'attachment' ) as $post_type ) {

		WP_CLI::line( "Deleting all posts of type \"$post_type\"..." );

		do {
			$posts = get_posts(
				array(
					'post_type'              => $post_type,
					'post_status'            => 'any',
					'numberposts'            => 100,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			if ( empty( $posts ) ) {
				break;
			}

			foreach ( $posts as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		} while ( $posts );
	}

	// Add some images

	$sideload_image = function ( string $image_url, ?string $filename = null ) {
		if ( null === $filename ) {
			$filename = sprintf( '%s.%s', uniqid( 'image-' ), pathinfo( $image_url, PATHINFO_EXTENSION ) );
		}

		WP_CLI::line( "Sideloading $image_url ($filename)..." );

		for ( $j = 0; $j < 20; $j++ ) {
			$image_tmp_file = download_url( $image_url );

			if ( is_wp_error( $image_tmp_file ) ) {
				continue;
			}

			break;
		}

		if ( is_wp_error( $image_tmp_file ) ) {
			WP_CLI::Error( 'Failed to download picsum image: ' . $image_tmp_file->get_error_message() );
		}

		$image_file_arr = array(
			'name'     => $filename,
			'tmp_name' => $image_tmp_file,
		);

		$image_id = media_handle_sideload( $image_file_arr, 0 );

		if ( file_exists( $image_tmp_file ) ) {
			unlink( $image_tmp_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}

		if ( is_wp_error( $image_id ) ) {
			WP_CLI::Error( $image_id->get_error_message() );
		}

		return $image_id;
	};

	WP_CLI::line( 'Sideloading generic images...' );

	$image_ids = array();

	for ( $i = 0; $i < 10; $i++ ) {
		$image_url = sprintf( 'https://picsum.photos/seed/%s/1920/1280.jpg', uniqid( '', true ) );

		$image_id = $sideload_image( $image_url );

		$image_ids[] = $image_id;
	}

	$get_rand_image = fn() => $image_ids[ array_rand( $image_ids ) ];

	// Set contact options

	WP_CLI::line( 'Setting contact options...' );

	update_option( '_options_contact-address', 'options_contact-address' );
	update_option( 'options_contact-address', "Berghs School of Communication\nBobergsgatan 48\n111 93 Stockholm" );

	update_option( '_options_contact-phone', 'options_contact-phone' );
	update_option( 'options_contact-phone', '+46 8 587 550 00' );

	foreach ( array(
		'youtube'   => 'https://www.youtube.com/channel/UCxtfLHrbAno08WVj-Ir-4GA',
		'tiktok'    => 'https://www.tiktok.com/@berghssoc',
		'instagram' => 'https://www.instagram.com/berghs/',
		'linkedin'  => 'https://se.linkedin.com/school/berghs-school-of-communication/',
		'facebook'  => 'https://www.facebook.com/BerghsSoC/',
	) as $service_slug => $service_url ) {
		update_option( "_options_contact-{$service_slug}_url", "app-options-contact-{$service_slug}_url" );
		update_option( "options_contact-{$service_slug}_url", $service_url );

	}

	// Set sponsor options

	WP_CLI::line( 'Setting sponsor options...' );

	$logo_ids = array(
		$sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/Djuce-Logo.png' ),
		$sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/deglabbet-logo.png' ),
		$sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/818_Logo_Lockup_HERO_Pina-1-1.png' ),
		$sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/tapdance_logo-1.png' ),
	);

	update_field( 'sponsors', array(), 'options' );

	update_option( '_options_sponsors', 'app-options-sponsors-sponsors' );
	update_option( 'options_sponsors', '10' );

	for ( $i = 0; $i < 10; $i++ ) {
		$sponsor_name  = mb_ucfirst( $faker->word() );
		$sponsor_image = $logo_ids[ $i % count( $logo_ids ) ];
		$sponsor_url   = sprintf( 'https://www.%s.com', sanitize_title( $sponsor_name ) );

		update_option( "_options_sponsors_{$i}_name", 'app-options-sponsors-sponsors-name' );
		update_option( "options_sponsors_{$i}_name", $sponsor_name );

		update_option( "_options_sponsors_{$i}_image", 'app-options-sponsors-sponsors-image' );
		update_option( "options_sponsors_{$i}_image", $sponsor_image );

		update_option( "_options_sponsors_{$i}_url", 'app-options-sponsors-sponsors-url' );
		update_option( "options_sponsors_{$i}_url", $sponsor_url );
	}

	// Insert projects

	WP_CLI::line( 'Inserting projects...' );

	for ( $i = 0; $i < 10; $i++ ) {
		WP_CLI::line( "Inserting project $i..." );
		$project_title              = ucwords( $faker->words( 5, true ) );
		$project_image              = $get_rand_image();
		$project_video              = 'https://vimeo.com/714785167';
		$project_team_members       = random_int( 3, 10 );
		$project_company            = ucwords( $faker->words( 5, true ) );
		$project_content_company    = $faker->paragraphs( 3, true );
		$project_content_background = $faker->paragraphs( 3, true );
		$project_content_solution   = $faker->paragraphs( 3, true );

		$meta_input = array(
			'image'              => $project_image,
			'video'              => $project_video,
			'company'            => $project_company,
			'team_members'       => $project_team_members,
			'content-company'    => $project_content_company,
			'content-background' => $project_content_background,
			'content-solution'   => $project_content_solution,
		);

		foreach ( array_keys( $meta_input ) as $meta_key ) {
			$meta_input[ '_' . $meta_key ] = 'app-post_type-project-' . $meta_key;
		}

		for ( $j = 0; $j < $project_team_members; $j++ ) {
			$project_team_member_name  = ucwords( $faker->words( 2, true ) );
			$project_team_member_class = $faker->randomElement(
				array(
					'AD',
					'CD',
					'CE',
					'CW',
					'DDS',
					'GM',
					'PL',
					'PR',
					'SK',
					'Tutor',
				)
			);

			$meta_input[ "_team_members_{$j}_name" ]  = 'app-post_type-project-team_members-name';
			$meta_input[ "team_members_{$j}_name" ]   = $project_team_member_name;
			$meta_input[ "_team_members_{$j}_class" ] = 'app-post_type-project-team_members-class';
			$meta_input[ "team_members_{$j}_class" ]  = $project_team_member_class;
		}

		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'   => 'project',
					'post_status' => 'publish',
					'post_title'  => esc_html( $project_title ),
					'meta_input'  => $meta_input,
				)
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_Cli::error( $post_id->get_error_message() );
		}
	}

	WP_CLI::line( 'Done.' );
}

WP_CLI::add_command(
	'seed',
	'_app_seed_command',
	array( 'shortdesc' => 'Seeds the database with test data.' ),
);

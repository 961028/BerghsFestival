<?php

declare(strict_types=1);

use Faker\Generator;

final class App_Seed_Command {
	private readonly Generator $faker;

	/** @var list<int> */
	private array $generic_image_ids = array();

	public function __construct() {
		$this->faker = Faker\Factory::create();
	}

	public function __invoke() {
		WP_CLI::line( 'Seeding...' );

		$this->delete_all_content();

		$this->sideload_generic_images();

		$home_page_id        = $this->insert_home_page();
		$experiences_page_id = $this->insert_experiences_page();
		$about_page_id       = $this->insert_about_page();

		$this->insert_primary_menu( $home_page_id, $experiences_page_id, $about_page_id );

		$this->set_contact_options();

		$this->set_sponsor_options();

		$this->insert_projects();

		WP_CLI::line( 'Done.' );
	}

	private function delete_all_content(): void {
		$this->delete_all_posts();
		$this->delete_all_terms();
		$this->delete_all_menus();
	}

	private function delete_all_posts(): void {
		$post_types = get_post_types();

		foreach ( $post_types as $post_type ) {
			$this->delete_all_posts_of_type( $post_type );
		}
	}

	private function delete_all_posts_of_type( string $post_type ): void {
		WP_CLI::line( "Deleting all posts of type \"$post_type\"" );

		do {
			$post_ids = get_posts(
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

			if ( empty( $post_ids ) ) {
				break;
			}

			foreach ( $post_ids as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		} while ( $post_ids );
	}

	private function delete_all_terms(): void {
		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$this->delete_all_terms_of_taxonomy( $taxonomy );
		}
	}

	private function delete_all_terms_of_taxonomy( string $taxonomy ): void {
		WP_CLI::line( "Deleting all terms of taxonomy \"$taxonomy\"" );

		do {
			$term_ids = get_terms(
				array(
					'taxonomy' => $taxonomy,
					'return'   => 'ids',
				)
			);

			if ( empty( $term_ids ) ) {
				break;
			}

			foreach ( $term_ids as $term_id ) {
				wp_delete_term( $term_id, $taxonomy );
			}
		} while ( $term_ids );
	}

	private function delete_all_menus(): void {
		foreach ( wp_get_nav_menus() as $menu ) {
			WP_CLI::line( "Deleting menu \"$menu->name\"" );

			wp_delete_nav_menu( $menu->term_id );
		}
	}

	private function sideload_image( string $image_url, ?string $filename = null ): int {
		if ( null === $filename ) {
			$filename = sprintf( '%s.%s', uniqid( 'image-' ), pathinfo( $image_url, PATHINFO_EXTENSION ) );
		}

		WP_CLI::line( "Sideloading $image_url ($filename)" );

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

		$image_id = media_handle_sideload( $image_file_arr );

		if ( file_exists( $image_tmp_file ) ) {
			unlink( $image_tmp_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}

		if ( is_wp_error( $image_id ) ) {
			WP_CLI::Error( $image_id->get_error_message() );
		}

		update_post_meta( $image_id, '_wp_attachment_image_alt', wp_slash( $this->faker->sentence() ) );

		return $image_id;
	}

	private function sideload_generic_images(): void {
		WP_CLI::line( 'Sideloading generic images' );

		$image_ids = array();

		for ( $i = 0; $i < 10; $i++ ) {
			$image_url = sprintf( 'https://picsum.photos/seed/%s/1920/1280.jpg', uniqid( '', true ) );

			$image_id = $this->sideload_image( $image_url );

			$image_ids[] = $image_id;
		}

		$this->generic_image_ids = $image_ids;
	}

	private function get_rand_generic_image(): int {
		return $this->generic_image_ids[ array_rand( $this->generic_image_ids ) ];
	}

	private function insert_post( array $postarr ): int {
		$post_id = wp_insert_post( wp_slash( $postarr ), true );

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::Error( $post_id->get_error_message() );
		}

		return $post_id;
	}

	private function insert_home_page(): int {
		WP_CLI::line( 'Inserting "Home" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Home',
				'post_content' => "This is the <strong>home</strong> page.\n\nWe'll fill it with content later.'",
				'meta_input'   => array(
					'_wp_page_template' => 'page-home.php',
				),
			)
		);

		$sections = array(
			array(
				'title'   => 'About',
				'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam posuere a nibh at porttitor. Ut nec nibh nec massa porta consequat eu a augue. Sed euismod ullamcorper nisi, at tempus ante dignissim sed. Sed nisl turpis, tincidunt sed fermentum id, ornare eget metus. Ut efficitur euismod tellus. Quisque et libero ipsum. Pellentesque consectetur massa vitae ipsum bibendum, eu mattis urna tempus. Morbi tempus iaculis odio, sed suscipit lorem aliquam in. Vestibulum nec feugiat magna, tempus congue ex. Quisque quam nibh, scelerisque nec purus eget, mollis aliquam ante.',
			),
			array(
				'title'   => 'Manifest',
				'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam posuere a nibh at porttitor. Ut nec nibh nec massa porta consequat eu a augue. Sed euismod ullamcorper nisi, at tempus ante dignissim sed. Sed nisl turpis, tincidunt sed fermentum id, ornare eget metus. Ut efficitur euismod tellus. Quisque et libero ipsum. Pellentesque consectetur massa vitae ipsum bibendum, eu mattis urna tempus. Morbi tempus iaculis odio, sed suscipit lorem aliquam in. Vestibulum nec feugiat magna, tempus congue ex. Quisque quam nibh, scelerisque nec purus eget, mollis aliquam ante.',
			),
		);
		update_field( _app_page_home_field_key( 'sections' ), $sections, $page_id );

		update_option( 'show_on_front', 'page', true );
		update_option( 'page_on_front', $page_id );

		return $page_id;
	}

	private function insert_experiences_page(): int {
		WP_CLI::line( 'Inserting "Experiences" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Experiences',
				'post_content' => "This is the <strong>Experiences</strong> page.\n\nWe'll fill it with content later.'",
				'meta_input'   => array(
					'_wp_page_template' => 'page-experiences.php',
				),
			)
		);

		$schedule = array();

		foreach ( array( 'Saturday', 'Sunday' ) as $day ) {
			$events = array();

			for ( $i = 0; $i < 10; $i++ ) {
				$events[] = array(
					'start_time' => sprintf( '%02d:00', $i + 10 ),
					'title'      => ucwords( $this->faker->words( 3, true ) ),
				);
			}

			$schedule[] = array(
				'day'    => $day,
				'events' => $events,
			);
		}

		update_field( _app_page_experiences_field_key( 'schedule' ), $schedule, $page_id );

		$group_configs = array(
			'Food & Drink'  => array(
				'label' => 'vendor',
				'url'   => true,
			),
			'Music'         => array(
				'label' => 'artist',
				'url'   => true,
			),
			'Installations' => array(
				'label' => 'installation',
				'url'   => false,
			),
		);

		$groups = array();

		foreach ( $group_configs as $group_title => $config ) {
			$group_description = wpautop( esc_html( $this->faker->paragraph() ) );

			$items = array();

			for ( $i = 0; $i < 10; $i++ ) {
				$item_name = ucwords( $this->faker->words( 3, true ) );

				$items[] = array(
					'name'        => $item_name,
					'image'       => $this->get_rand_generic_image(),
					'description' => wpautop( esc_html( $this->faker->paragraph() ) ),
					'url'         => $config['url'] && 0 === $i % 3 ? sprintf( 'https://www.%s.com/', sanitize_title( $item_name ) ) : '',
				);
			}

			$groups[] = array(
				'title'       => $group_title,
				'description' => $group_description,
				'items'       => $items,
			);
		}

		update_field( _app_page_experiences_field_key( 'groups' ), $groups, $page_id );

		return $page_id;
	}

	private function insert_about_page(): int {
		WP_CLI::line( 'Inserting "About Berghs" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'About Berghs',
				'meta_input'  => array(
					'_wp_page_template' => 'page-about-berghs.php',
				),
			)
		);

		$sections = array(
			array(
				'title'   => 'Action-based learning',
				'content' => 'At Berghs, our passion for communication in all its forms drives us to educate and nurture the brightest communicators of the future, a mission we\'ve proudly upheld since 1941. We are dedicated to delivering top-quality education, ensuring that everyone can benefit fully from our teachings. Our approach centers on development and inclusion, seeking creative ways to address real-world problems. We believe in the positive impact of diverse skills, experiences, and perspectives on problem-solving. We celebrate the power of creativity and teamwork!

<ul>
	<li>real client cases to practise new knowledge</li>
	<li>giving insight into practical application</li>
	<li>collaboration and group dynamics</li>
</ul>',
			),
			array(
				'title'   => 'Perspective',
				'content' => 'Bringing together students from different disciplines with a variety of backgrounds, experiences, and perspectives opens doors to increased creativity and understanding. Diversity promotes empathy and respect as it fosters critical thinking by challenging thought patterns and encouraging deeper discussions. Inclusion ensures that every individual feels seen and heard, boosting self-esteem and motivation. We believe that cooperation and understanding are crucial in today\'s society.',
			),
			array(
				'title'   => 'Applied learning and amplified intelligence',
				'content' => 'At Berghs, we continually strive to be at the forefront of education and pedagogy. New methods, research, and digital tools, including amplified intelligence (AI), enable innovative ways of sharing and applying knowledge. Applied learning, sufficient room for reflection, and feedback are crucial to us. We approach theories and models pragmatically, emphasizing their practical application. We thrive on tasks, workshops, and group cases to share experiences, with no traditional exams and minimal theory. Our focus is within real-life, case-based problems – where conversation and collaboration are central!',
			),
		);
		update_field( _app_page_about_berghs_field_key( 'sections' ), $sections, $page_id );

		update_field( _app_page_about_berghs_field_key( 'learn_more', 'cta' ), 'Learn more about Berghs', $page_id );
		update_field( _app_page_about_berghs_field_key( 'learn_more', 'url' ), 'https://www.berghs.se/om-berghs/', $page_id );

		return $page_id;
	}

	private function insert_primary_menu(
		int $home_page_id,
		int $experiences_page_id,
		int $about_page_id,
	): void {
		WP_CLI::line( 'Inserting primary menu' );

		foreach ( wp_get_nav_menus() as $menu ) {
			wp_delete_nav_menu( $menu->term_id );
		}

		$primary_menu_id = $this->create_menu( 'Primary' );

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $home_page_id,
				'menu-item-title'     => '',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $experiences_page_id,
				'menu-item-title'     => '',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'   => 'post_type_archive',
				'menu-item-object' => 'project',
				'menu-item-title'  => 'Projects',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $about_page_id,
				'menu-item-title'     => '',
			)
		);

		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $primary_menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	private function create_menu( string $name ): int {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			WP_CLI::Error( $menu_id );
		}

		return $menu_id;
	}

	private function create_menu_item( int $menu_id, array $args ): int {
		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-status' => 'publish',
				...$args,
			)
		);

		if ( is_wp_error( $item_id ) ) {
			WP_CLI::Error( $item_id );
		}

		return $item_id;
	}

	private function set_contact_options(): void {
		WP_CLI::line( 'Setting contact options' );

		$name = "Berghs School of Communication\nBobergsgatan 48\n111 93 Stockholm";
		update_field( _app_contact_field_key( 'name' ), $name, 'options' );

		$phone = '+46 8 587 550 00';
		update_field( _app_contact_field_key( 'phone' ), $phone, 'options' );

		$service_slug_to_url = array(
			'youtube'   => 'https://www.youtube.com/channel/UCxtfLHrbAno08WVj-Ir-4GA',
			'tiktok'    => 'https://www.tiktok.com/@berghssoc',
			'instagram' => 'https://www.instagram.com/berghs/',
			'linkedin'  => 'https://se.linkedin.com/school/berghs-school-of-communication/',
			'facebook'  => 'https://www.facebook.com/BerghsSoC/',
		);
		foreach ( $service_slug_to_url as $service_slug => $service_url ) {
			update_field( _app_contact_field_key( $service_slug, 'url' ), $service_url, 'options' );
		}
	}

	private function set_sponsor_options(): void {
		WP_CLI::line( 'Setting sponsor options' );

		$logo_ids = array(
			$this->sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/Djuce-Logo.png' ),
			$this->sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/deglabbet-logo.png' ),
			$this->sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/818_Logo_Lockup_HERO_Pina-1-1.png' ),
			$this->sideload_image( 'https://unfinishedfestival.se/wp-content/uploads/2024/05/tapdance_logo-1.png' ),
		);

		$sponors = array();
		for ( $i = 0; $i < 10; $i++ ) {
			$sponsor_name  = ucwords( $this->faker->word() );
			$sponsor_image = $logo_ids[ $i % count( $logo_ids ) ];
			$sponsor_url   = sprintf( 'https://www.%s.com', sanitize_title( $sponsor_name ) );

			$sponors[] = array(
				'name'  => $sponsor_name,
				'image' => $sponsor_image,
				'url'   => $sponsor_url,
			);
		}
		update_field( _app_sponsors_field_key( 'sponsors' ), $sponors, 'options' );
	}

	private function iq_options(): void {
		WP_CLI::line( 'Setting IQ options' );

		$title = 'Drink Responsibly';
		update_field( _app_iq_field_key( 'title' ), $title, 'options' );

		$content = wpautop( esc_html( "We strive to promote responsible alcohol consumption and encourage all our participants to make conscious and healthy choices.\n\nBeverage sales at the event are for individuals over 18 years old, and we also offer a wide range of non-alcoholic options to ensure everyone can enjoy the experience.\n\nFor more information on responsible alcohol consumption, visit <a href=\"https://iq.se\" target=\"_blank\">IQ.se</a>." ) );
		update_field( _app_iq_field_key( 'content' ), $content, 'options' );
	}

	private function insert_projects(): void {
		WP_CLI::line( 'Inserting projects' );

		for ( $i = 0; $i < 30; $i++ ) {
			$this->insert_project( $i );
		}
	}

	private function insert_project( int $i ): void {
		WP_CLI::line( "Inserting project $i" );

		$title = ucwords( $this->faker->words( 5, true ) );

		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_type'   => 'project',
					'post_status' => 'publish',
					'post_title'  => esc_html( $title ),
				)
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_Cli::error( $post_id->get_error_message() );
		}

		$company = ucwords( $this->faker->words( 3, true ) );
		update_field( _app_projects_field_key( 'company' ), $company, $post_id );

		$image = $this->get_rand_generic_image();
		update_field( _app_projects_field_key( 'image' ), $image, $post_id );

		$video = 'https://vimeo.com/714785167';
		update_field( _app_projects_field_key( 'video' ), $video, $post_id );

		$num_team_members = random_int( 5, 10 );
		$team_members     = array();
		for ( $j = 0; $j < $num_team_members; $j++ ) {
			$team_member_name  = ucwords( $this->faker->words( 2, true ) );
			$team_member_class = $this->faker->randomElement(
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

			$team_members[] = array(
				'name'  => $team_member_name,
				'class' => $team_member_class,
			);
		}
		update_field( _app_projects_field_key( 'team_members' ), $team_members, $post_id );

		$content_company = $this->faker->paragraphs( 3, true );
		update_field( _app_projects_field_key( 'content', 'company' ), $content_company, $post_id );

		$content_background = $this->faker->paragraphs( 3, true );
		update_field( _app_projects_field_key( 'content', 'background' ), $content_background, $post_id );

		$content_solution = $this->faker->paragraphs( 3, true );
		update_field( _app_projects_field_key( 'content', 'solution' ), $content_solution, $post_id );
	}
}

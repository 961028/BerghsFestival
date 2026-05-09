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

		$home_page_id          = $this->insert_home_page();
		$schedule_page_id      = $this->insert_schedule_page();
		$music_page_id         = $this->insert_music_page();
		$installations_page_id = $this->insert_installations_page();
		$food_page_id          = $this->insert_food_page();
		$about_page_id         = $this->insert_about_page();

		$this->insert_primary_menu(
			$home_page_id,
			$schedule_page_id,
			$music_page_id,
			$installations_page_id,
			$food_page_id,
		);

		$this->set_contact_options();
		$this->set_seo_options();
		$this->set_iq_options();
		$this->set_photo_notice_options();
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
			WP_CLI::Error( 'Failed to download image: ' . $image_tmp_file->get_error_message() );
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

		for ( $i = 0; $i < 6; $i++ ) {
			$image_url = sprintf( 'https://picsum.photos/seed/%s/1920/1280.jpg', uniqid( '', true ) );

			$image_ids[] = $this->sideload_image( $image_url );
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
				'post_name'    => 'home',
				'post_content' => '',
				'meta_input'   => array(
					'_wp_page_template' => 'page-home.php',
				),
			)
		);

		// Hero — no real video supplied yet ("Samma som innan"); leave empty,
		// editor can upload via WP admin.
		update_field( _app_page_home_field_key( 'hero', 'image' ), $this->get_rand_generic_image(), $page_id );

		// Sections.
		$sections = array(
			array(
				'title'   => 'About',
				'content' => wpautop(
					'We have never had more tools, more data, or more ways to create. Yet much of what brands do feels increasingly similar. Risk is filtered out early. Ideas are measured before they are fully formed. What has worked before becomes the safest argument for what should exist next. The result is a landscape filled with work that performs, but rarely surprises, challenges or shifts perception.

Through installations that deliberately challenge familiar rules across disciplines, the festival introduces friction: moments where authenticity replaces optimisation, where the imperfect replaces the overpolished, and where bravery replaces conformity.

The festival dissolves the boundary between creator and observer, encouraging visitors to explore and define what bold decision-making is today.'
				),
			),
			array(
				'title'   => 'Manifesto',
				'content' => wpautop(
					'this is a response to smoothness fatigue everything became seamless payments seamless communication seamless identity seamless even outrage comes with formats we forgot how to hesitate how to disagree without optimizing the tone friction brings back resistance the productive kind the kind that makes new language necessary not louder just less filtered not clearer just more honest intentional imperfection we dont gather to consume we gather to complicate to question why culture feels pre-chewed why creativity is always asked to explain itself why everything must be immediately understood confusion is useful contradiction is productive tension is where meaning actually forms evidence of choice proof that something is happening finally'
				),
			),
		);
		update_field( _app_page_home_field_key( 'sections' ), $sections, $page_id );

		// Festival details.
		update_field( _app_page_home_field_key( 'register_label' ), 'Register now ->', $page_id );
		update_field( _app_page_home_field_key( 'registration_url' ), '', $page_id );
		update_field( _app_page_home_field_key( 'opening_date' ), '2026-05-22 12:00:00', $page_id );
		update_field( _app_page_home_field_key( 'venue' ), 'Bobergsgatan 48', $page_id );
		update_field( _app_page_home_field_key( 'student_count' ), 142, $page_id );
		update_field( _app_page_home_field_key( 'project_count' ), 22, $page_id );

		$festival_days = array(
			array(
				'abbr'  => 'Fri',
				'date'  => '22',
				'hours' => '12:00 – 01:00',
			),
			array(
				'abbr'  => 'Sat',
				'date'  => '23',
				'hours' => '12:00 – 18:00',
			),
		);
		update_field( _app_page_home_field_key( 'festival_days' ), $festival_days, $page_id );

		update_option( 'show_on_front', 'page', true );
		update_option( 'page_on_front', $page_id );

		return $page_id;
	}

	private function insert_schedule_page(): int {
		WP_CLI::line( 'Inserting "Schedule" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Schedule',
				'post_name'    => 'schedule',
				'post_content' => wpautop(
					'two days of festival & graduation exhibition, plan your visit below.

Friday 22 May, 12:00–01:00
Saturday 23 May, 12:00–18:00'
				),
				'meta_input'   => array(
					'_wp_page_template' => 'page-schedule.php',
					'meta_description' => 'Full schedule for Friction Festival, Stockholm. Installations, live acts and happenings across 22–23 May 2026. Plan your visit.',
				),
			)
		);

		$schedule = array(
			array(
				'day'    => 'Friday',
				'events' => array(
					array(
						'artist'     => '',
						'start_time' => '12:00',
						'title'      => 'Doors open',
					),
				),
			),
			array(
				'day'    => 'Saturday',
				'events' => array(
					array(
						'artist'     => '',
						'start_time' => '12:00',
						'title'      => 'Doors open',
					),
				),
			),
		);
		update_field( _app_page_schedule_field_key( 'schedule' ), $schedule, $page_id );

		return $page_id;
	}

	private function insert_music_page(): int {
		WP_CLI::line( 'Inserting "Music" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Music',
				'post_name'    => 'music',
				'post_content' => wpautop(
					'djs and live acts across both days — friday night runs til 1am, saturday sets carry you through the afternoon. check the schedule below and find your spot on the dancefloor.'
				),
				'meta_input'   => array(
					'_wp_page_template' => 'page-experience-music.php',
					'meta_description' => 'Live acts and DJ sets across both festival days. Friday night runs til 1am. Full lineup below. Friction Festival, Stockholm, 22–23 May 2026.',
				),
			)
		);

		$days      = array( 'Friday', 'Saturday' );
		$locations = array( 'Ljusgården', 'Aulan', 'Pink Room', 'Gränden', 'Receptionen' );

		$artists = array();

		for ( $i = 0; $i < 12; $i++ ) {
			$name = ucwords( $this->faker->words( random_int( 1, 3 ), true ) );

			$show_count = random_int( 1, 2 );
			$shows      = array();

			for ( $j = 0; $j < $show_count; $j++ ) {
				$start_hour      = random_int( 12, 21 );
				$start_minute    = $this->faker->randomElement( array( 0, 30 ) );
				$duration_min    = $this->faker->randomElement( array( 30, 45, 60, 75, 90, 120 ) );
				$end_total_min   = ( $start_hour * 60 ) + $start_minute + $duration_min;
				$end_hour        = intdiv( $end_total_min, 60 );
				$end_minute      = $end_total_min % 60;

				$shows[] = array(
					'day'        => $this->faker->randomElement( $days ),
					'start_time' => sprintf( '%02d:%02d', $start_hour, $start_minute ),
					'end_time'   => sprintf( '%02d:%02d', $end_hour, $end_minute ),
					'location'   => $this->faker->randomElement( $locations ),
				);
			}

			$artists[] = array(
				'name'        => $name,
				'image'       => $this->get_rand_generic_image(),
				'description' => wpautop( esc_html( $this->faker->paragraph() ) ),
				'url'         => 0 === $i % 3 ? sprintf( 'https://www.%s.com/', sanitize_title( $name ) ) : '',
				'social_url'  => 0 === $i % 2 ? sprintf( 'https://www.instagram.com/%s/', sanitize_title( $name ) ) : '',
				'shows'       => $shows,
			);
		}

		update_field( _app_page_experience_music_field_key( 'artists' ), $artists, $page_id );

		return $page_id;
	}

	private function insert_installations_page(): int {
		WP_CLI::line( 'Inserting "Installations" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Installations',
				'post_name'    => 'installations',
				'post_content' => wpautop(
					'a series of installations spread throughout the school. some ask you to participate. others ask you to react. none are meant to be passively consumed. grab a map and step into the friction.'
				),
				'meta_input'   => array(
					'_wp_page_template' => 'page-experience-installations.php',
					'meta_description' => '22 interactive installations and happenings at Berghs. Touch things, enter things, get a tattoo. Friction Festival, Stockholm, 22–23 May 2026.',
				),
			)
		);

		// Items repeater intentionally left empty — the TSV has no installation items yet.
		update_field( _app_page_experience_installations_field_key( 'items' ), array(), $page_id );

		return $page_id;
	}

	private function insert_food_page(): int {
		WP_CLI::line( 'Inserting "Food & drink" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Food & drink',
				'post_name'    => 'food-drink',
				'post_content' => wpautop(
					'bars throughout the venue pour cocktails from 818 tequila, le tribute, coppa cocktails and more. food on site both days. non-alcoholic options are always available. card only.'
				),
				'meta_input'   => array(
					'_wp_page_template' => 'page-experience-food.php',
					'meta_description' => 'Cocktails, beer, food and more on site both days. Non-alcoholic options available. Card only. Stockholm, 22–23 May 2026.',
				),
			)
		);

		update_field( _app_page_experience_food_field_key( 'items' ), array(), $page_id );

		return $page_id;
	}

	private function insert_about_page(): int {
		WP_CLI::line( 'Inserting "About Berghs" page' );

		$page_id = $this->insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'About Berghs',
				'post_name'   => 'about-berghs',
				'meta_input'  => array(
					'_wp_page_template' => 'page-about-berghs.php',
				),
			)
		);

		$sections = array(
			array(
				'title'   => 'Power of creativity',
				'content' => wpautop(
					'At Berghs, our passion for communication in all its forms drives us to educate and nurture the brightest communicators of the future, a mission we\'ve proudly upheld since 1941. We are dedicated to delivering top-quality education, ensuring that everyone can benefit fully from our teachings. Our approach centers on development and inclusion, seeking creative ways to address real-world problems. We believe in the positive impact of diverse skills, experiences, and perspectives on problem-solving. We celebrate the power of creativity and teamwork!'
				),
			),
			array(
				'title'   => 'Action-based learning',
				'content' => wpautop(
					'Action-based learning is practical and pragmatic learning through action, not just theory. We learn by continuously testing, making the learning process iterative: test, evaluate, improve. Mistakes become opportunities to learn, and reflection completes the learning. Practical work strengthens the ability to make smart decisions! At Berghs, we apply this through:

<ul>
	<li>real client cases to practise new knowledge</li>
	<li>giving insight into practical application</li>
	<li>collaboration and group dynamics</li>
</ul>'
				),
			),
			array(
				'title'   => 'Perspective',
				'content' => wpautop(
					'Bringing together students from different disciplines with a variety of backgrounds, experiences, and perspectives opens doors to increased creativity and understanding. Diversity promotes empathy and respect as it fosters critical thinking by challenging thought patterns and encouraging deeper discussions. Inclusion ensures that every individual feels seen and heard, boosting self-esteem and motivation. We believe that cooperation and understanding are crucial in today\'s society.'
				),
			),
			array(
				'title'   => 'Applied learning and amplified intelligence',
				'content' => wpautop(
					'At Berghs, we continually strive to be at the forefront of education and pedagogy. New methods, research, and digital tools, including amplified intelligence (AI), enable innovative ways of sharing and applying knowledge. Applied learning, sufficient room for reflection, and feedback are crucial to us. We approach theories and models pragmatically, emphasizing their practical application. We thrive on tasks, workshops, and group cases to share experiences, with no traditional exams and minimal theory. Our focus is within real-life, case-based problems – where conversation and collaboration are central!'
				),
			),
		);
		update_field( _app_page_about_berghs_field_key( 'sections' ), $sections, $page_id );

		update_field( _app_page_about_berghs_field_key( 'learn_more', 'cta' ), 'Learn more about Berghs', $page_id );
		update_field( _app_page_about_berghs_field_key( 'learn_more', 'url' ), 'https://www.berghs.se/om-berghs/', $page_id );

		return $page_id;
	}

	private function insert_primary_menu(
		int $home_page_id,
		int $schedule_page_id,
		int $music_page_id,
		int $installations_page_id,
		int $food_page_id,
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

		// "Experiences" parent — label only, no link.
		$experiences_item_id = $this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'  => 'custom',
				'menu-item-url'   => '#',
				'menu-item-title' => 'Experiences',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $schedule_page_id,
				'menu-item-parent-id' => $experiences_item_id,
				'menu-item-title'     => '',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $music_page_id,
				'menu-item-parent-id' => $experiences_item_id,
				'menu-item-title'     => '',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $installations_page_id,
				'menu-item-parent-id' => $experiences_item_id,
				'menu-item-title'     => '',
			)
		);

		$this->create_menu_item(
			$primary_menu_id,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $food_page_id,
				'menu-item-parent-id' => $experiences_item_id,
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

		update_field( _app_contact_field_key( 'name' ), 'Berghs School of Communication', 'options' );
		update_field( _app_contact_field_key( 'phone' ), '08-587 550 00', 'options' );
		update_field( _app_contact_field_key( 'email' ), 'info@berghs.se', 'options' );

		$service_slug_to_url = array(
			'instagram' => 'https://www.instagram.com/berghs/',
			'facebook'  => 'https://www.facebook.com/BerghsSoC/',
			'linkedin'  => 'https://se.linkedin.com/school/berghs-school-of-communication/',
			'tiktok'    => 'https://www.tiktok.com/@berghssoc',
			'youtube'   => 'https://www.youtube.com/channel/UCxtfLHrbAno08WVj-Ir-4GA',
		);

		foreach ( $service_slug_to_url as $service_slug => $service_url ) {
			update_field( _app_contact_field_key( $service_slug, 'url' ), $service_url, 'options' );
		}
	}

	private function set_seo_options(): void {
		WP_CLI::line( 'Setting SEO options' );

		update_field(
			_app_seo_field_key( 'seo', 'meta_description' ),
			'On May 22-23, 2026, Berghs Festival invites you to explore Friction - two days of inspiring encounters, challenging perspectives, and new ways of thinking.',
			'options'
		);
	}

	private function set_iq_options(): void {
		WP_CLI::line( 'Setting IQ options' );

		update_field( _app_footer_text_block_field_key( 'iq', 'title' ), 'Drink with intention', 'options' );

		$content = 'At Friction Festival, we encourage conscious participation and responsible consumption. Beverage sales are restricted to guests over 18, and a range of non-alcoholic options will be available. Look after yourself and the people around you.

For more on responsible alcohol habits, visit <a href="http://iq.se/" target="_blank" rel="noopener noreferrer">IQ.se</a>.';

		update_field( _app_footer_text_block_field_key( 'iq', 'content' ), wpautop( $content ), 'options' );
	}

	private function set_photo_notice_options(): void {
		WP_CLI::line( 'Setting photo notice options' );

		update_field( _app_footer_text_block_field_key( 'photo-notice', 'title' ), 'Photo notice', 'options' );

		$content = 'Parts of the event will be photographed and filmed for communication and marketing purposes across Berghs channels. If you prefer not to appear in any footage or imagery, please email <a href="mailto:studentbyran@berghs.se">studentbyran@berghs.se</a>.

Thank you for helping us share the experience.';

		update_field( _app_footer_text_block_field_key( 'photo-notice', 'content' ), wpautop( $content ), 'options' );
	}

	private function set_sponsor_options(): void {
		WP_CLI::line( 'Setting sponsor options' );

		// Drive share links resolve via the uc?export=download form. The Deglabbet logo
		// in the TSV is a Gmail attachment URL that requires auth, so we substitute a
		// public copy hosted elsewhere.
		$sponsors_data = array(
			array(
				'name'      => 'Deglabbet',
				'url'       => 'https://deglabbet.se',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1GFKdPmDaOvmqLESm5XVZPMxW-kONWFRg',
			),
			array(
				'name'      => 'Still Gin',
				'url'       => 'https://eu.dreandsnoop.com/sv-se/products/still-gin',
				'image_url' => 'https://drive.google.com/uc?export=download&id=17XhPlTTsgbFA5PWKpkqkOTve3-xgwTl_',
			),
			array(
				'name'      => 'Coppa Cocktails',
				'url'       => 'https://www.coppacocktails.com',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1ZyGi4_7h_KbGf2kYpu_a0J5HsyLJhDwx',
			),
			array(
				'name'      => '818 Tequila',
				'url'       => 'https://drink818.com',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1ouuWZHZN-TnpcS3vUL-lqlyF9K8hAH6V',
			),
			array(
				'name'      => 'Le Tribute',
				'url'       => 'https://letribute.com',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1D3qSNWJZ5pgxwYW23KB6lHFQgSqn4aya',
			),
			array(
				'name'      => 'QBN Spirit Connoisseurs',
				'url'       => 'https://www.qbnbev.com/en/',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1toMBM6bJQZVn-WDZM40yKdLY7WZoDuoE',
			),
			array(
				'name'      => 'Simple Marketing',
				'url'       => 'https://simplemarketing.se',
				'image_url' => 'https://drive.google.com/uc?export=download&id=1jJXH8F0NLeH2mtvrW7SAXxbGMFe61jFF',
			),
		);

		$sponsors = array();

		foreach ( $sponsors_data as $row ) {
			$image_id = $this->sideload_image(
				$row['image_url'],
				sprintf( 'sponsor-%s.png', sanitize_title( $row['name'] ) )
			);

			$sponsors[] = array(
				'name'  => $row['name'],
				'image' => $image_id,
				'url'   => $row['url'],
			);
		}

		update_field( _app_sponsors_field_key( 'sponsors' ), $sponsors, 'options' );
	}

	private function insert_projects(): void {
		WP_CLI::line( 'Inserting projects' );

		for ( $i = 0; $i < 30; $i++ ) {
			$this->insert_project( $i );
		}
	}

	private function insert_project( int $i ): void {
		$title = ucwords( $this->faker->words( 5, true ) );

		WP_CLI::line( "Inserting project $i ($title)" );

		$post_id = $this->insert_post(
			array(
				'post_type'   => 'project',
				'post_status' => 'publish',
				'post_title'  => $title,
			)
		);

		update_field( _app_projects_field_key( 'meta_description' ), $this->faker->sentence(), $post_id );
		update_field( _app_projects_field_key( 'project_type' ), $this->faker->randomElement( array( 'group', 'individual' ) ), $post_id );
		update_field( _app_projects_field_key( 'company' ), ucwords( $this->faker->words( 3, true ) ), $post_id );
		update_field( _app_projects_field_key( 'image' ), $this->get_rand_generic_image(), $post_id );
		update_field( _app_projects_field_key( 'video' ), 'https://vimeo.com/714785167', $post_id );

		$class_choices = array( 'AD', 'CD', 'CE', 'CW', 'DDS', 'GM', 'PL', 'PR', 'SK', 'Tutor' );
		$team_members  = array();
		$num_members   = random_int( 5, 10 );
		for ( $j = 0; $j < $num_members; $j++ ) {
			$team_members[] = array(
				'name'  => ucwords( $this->faker->words( 2, true ) ),
				'class' => $this->faker->randomElement( $class_choices ),
			);
		}
		update_field( _app_projects_field_key( 'team_members' ), $team_members, $post_id );

		update_field( _app_projects_field_key( 'content', 'company' ), $this->faker->paragraphs( 3, true ), $post_id );
		update_field( _app_projects_field_key( 'content', 'background' ), $this->faker->paragraphs( 3, true ), $post_id );
		update_field( _app_projects_field_key( 'content', 'solution' ), $this->faker->paragraphs( 3, true ), $post_id );
	}
}

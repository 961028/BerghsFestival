<?php
/**
 * Berghs Festival 2026 — Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BERGHS_VERSION', '1.0.0' );

/* ---------- Theme Setup ---------- */

add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'script', 'style' ] );
    add_theme_support( 'custom-logo', [ 'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true ] );

    add_image_size( 'project-card', 600, 400, true );
    add_image_size( 'project-hero', 1200, 800, true );
    add_image_size( 'sponsor-logo', 240, 120, false );

    register_nav_menus( [
        'primary' => 'Primary Navigation',
        'footer'  => 'Footer Navigation',
    ] );
});

/* ---------- Assets ---------- */

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'berghs-style', get_stylesheet_uri(), [], BERGHS_VERSION );
    wp_enqueue_script( 'berghs-scripts', get_template_directory_uri() . '/assets/js/main.js', [], BERGHS_VERSION, true );
});

/* ---------- Custom Post Types ---------- */

add_action( 'init', function () {

    register_post_type( 'project', [
        'labels'       => [
            'name' => 'Projects', 'singular_name' => 'Project',
            'add_new_item' => 'Add New Project', 'edit_item' => 'Edit Project',
            'menu_name' => 'Projects',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'projects' ],
        'menu_icon'    => 'dashicons-portfolio',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'show_in_rest' => true,
    ] );

    register_post_type( 'installation', [
        'labels'       => [
            'name' => 'Installations', 'singular_name' => 'Installation',
            'add_new_item' => 'Add New Installation', 'edit_item' => 'Edit Installation',
            'menu_name' => 'Installations',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'installations' ],
        'menu_icon'    => 'dashicons-art',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'show_in_rest' => true,
    ] );

    register_post_type( 'sponsor', [
        'labels'       => [ 'name' => 'Sponsors', 'singular_name' => 'Sponsor', 'menu_name' => 'Sponsors' ],
        'public'       => false,
        'show_ui'      => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-money-alt',
        'supports'     => [ 'title', 'thumbnail' ],
        'show_in_rest' => true,
    ] );

    register_post_type( 'schedule_item', [
        'labels'       => [ 'name' => 'Schedule', 'singular_name' => 'Schedule Item', 'menu_name' => 'Schedule' ],
        'public'       => false,
        'show_ui'      => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-clock',
        'supports'     => [ 'title' ],
        'show_in_rest' => true,
    ] );

    register_taxonomy( 'program', 'project', [
        'labels'       => [ 'name' => 'Programs', 'singular_name' => 'Program', 'menu_name' => 'Programs' ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'program' ],
        'show_in_rest' => true,
    ] );
});

/* ---------- Meta Boxes ---------- */

add_action( 'add_meta_boxes', function () {
    add_meta_box( 'berghs_project_details', 'Project Details', 'berghs_project_meta_box', 'project', 'normal', 'high' );
    add_meta_box( 'berghs_schedule_details', 'Schedule Details', 'berghs_schedule_meta_box', 'schedule_item', 'normal', 'high' );
    add_meta_box( 'berghs_sponsor_details', 'Sponsor Details', 'berghs_sponsor_meta_box', 'sponsor', 'normal', 'high' );
    add_meta_box( 'berghs_hero_settings', 'Hero Settings', 'berghs_hero_meta_box', 'page', 'normal', 'high' );
});

/* --- Project Meta Box --- */

function berghs_project_meta_box( $post ) {
    wp_nonce_field( 'berghs_project', '_berghs_project_nonce' );
    $f = function( $k ) use ( $post ) { return get_post_meta( $post->ID, $k, true ); };
    $members = json_decode( $f( 'project_group_members' ), true ) ?: [];
    ?>
    <table class="form-table">
        <tr><th><label for="project_company">Company</label></th>
            <td><input type="text" id="project_company" name="project_company" value="<?php echo esc_attr( $f('project_company') ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="project_background">Background</label></th>
            <td><textarea id="project_background" name="project_background" rows="3" class="large-text" maxlength="500"><?php echo esc_textarea( $f('project_background') ); ?></textarea></td></tr>
        <tr><th><label for="project_solution">Solution</label></th>
            <td><textarea id="project_solution" name="project_solution" rows="3" class="large-text" maxlength="500"><?php echo esc_textarea( $f('project_solution') ); ?></textarea></td></tr>
        <tr><th><label for="project_result">Result</label></th>
            <td><textarea id="project_result" name="project_result" rows="3" class="large-text" maxlength="500"><?php echo esc_textarea( $f('project_result') ); ?></textarea></td></tr>
        <tr><th><label for="project_case_film">Case Film URL</label></th>
            <td><input type="url" id="project_case_film" name="project_case_film" value="<?php echo esc_url( $f('project_case_film') ); ?>" class="regular-text"></td></tr>
        <tr><th>Group Members</th>
            <td>
                <div id="berghs-members-repeater">
                    <?php foreach ( $members as $m ) : ?>
                        <div style="margin-bottom:6px;display:flex;gap:6px">
                            <input type="text" name="project_members_name[]" value="<?php echo esc_attr( $m['name'] ?? '' ); ?>" placeholder="Name" style="width:200px">
                            <input type="text" name="project_members_program[]" value="<?php echo esc_attr( $m['program_class'] ?? '' ); ?>" placeholder="Program / Class" style="width:200px">
                            <button type="button" class="button berghs-remove-member">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="berghs-add-member">+ Add Member</button>
                <script>
                document.addEventListener('DOMContentLoaded',function(){
                    var w=document.getElementById('berghs-members-repeater');
                    document.getElementById('berghs-add-member').addEventListener('click',function(){
                        var r=document.createElement('div');
                        r.style.cssText='margin-bottom:6px;display:flex;gap:6px';
                        r.innerHTML='<input type="text" name="project_members_name[]" placeholder="Name" style="width:200px"><input type="text" name="project_members_program[]" placeholder="Program / Class" style="width:200px"><button type="button" class="button berghs-remove-member">&times;</button>';
                        w.appendChild(r);
                    });
                    w.addEventListener('click',function(e){if(e.target.classList.contains('berghs-remove-member'))e.target.parentElement.remove();});
                });
                </script>
            </td></tr>
        <tr><th><label for="project_is_individual">Individual Project</label></th>
            <td><label><input type="checkbox" id="project_is_individual" name="project_is_individual" value="1" <?php checked( $f('project_is_individual'), '1' ); ?>> This is an individual project</label></td></tr>
    </table>
    <?php
}

/* --- Schedule Meta Box --- */

function berghs_schedule_meta_box( $post ) {
    wp_nonce_field( 'berghs_schedule', '_berghs_schedule_nonce' );
    $f = function( $k ) use ( $post ) { return get_post_meta( $post->ID, $k, true ); };
    $types = [ 'food' => 'Food', 'performance' => 'Performance', 'activity' => 'Activity', 'ceremony' => 'Ceremony', 'other' => 'Other' ];
    ?>
    <table class="form-table">
        <tr><th><label for="schedule_time">Time</label></th>
            <td><input type="text" id="schedule_time" name="schedule_time" value="<?php echo esc_attr( $f('schedule_time') ); ?>" class="regular-text" placeholder="e.g. 14:00 or 14:00-15:30"></td></tr>
        <tr><th><label for="schedule_description">Description</label></th>
            <td><textarea id="schedule_description" name="schedule_description" rows="2" class="large-text"><?php echo esc_textarea( $f('schedule_description') ); ?></textarea></td></tr>
        <tr><th><label for="schedule_type">Type</label></th>
            <td><select id="schedule_type" name="schedule_type">
                <?php foreach ( $types as $v => $l ) : ?>
                    <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $f('schedule_type'), $v ); ?>><?php echo esc_html( $l ); ?></option>
                <?php endforeach; ?>
            </select></td></tr>
        <tr><th><label for="schedule_sort_order">Sort Order</label></th>
            <td><input type="number" id="schedule_sort_order" name="schedule_sort_order" value="<?php echo esc_attr( $f('schedule_sort_order') ?: '0' ); ?>" class="small-text"></td></tr>
    </table>
    <?php
}

/* --- Sponsor Meta Box --- */

function berghs_sponsor_meta_box( $post ) {
    wp_nonce_field( 'berghs_sponsor', '_berghs_sponsor_nonce' );
    $url = get_post_meta( $post->ID, 'sponsor_url', true );
    ?>
    <table class="form-table">
        <tr><th><label for="sponsor_url">Website URL</label></th>
            <td><input type="url" id="sponsor_url" name="sponsor_url" value="<?php echo esc_url( $url ); ?>" class="regular-text"></td></tr>
    </table>
    <?php
}

/* --- Hero Meta Box (front page only) --- */

function berghs_hero_meta_box( $post ) {
    $front = (int) get_option( 'page_on_front' );
    if ( $front > 0 && $post->ID !== $front ) return;
    wp_nonce_field( 'berghs_hero', '_berghs_hero_nonce' );
    $f = function( $k ) use ( $post ) { return get_post_meta( $post->ID, $k, true ); };
    ?>
    <table class="form-table">
        <tr><th><label for="hero_title">Title</label></th>
            <td><input type="text" id="hero_title" name="hero_title" value="<?php echo esc_attr( $f('hero_title') ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="hero_subtitle">Subtitle</label></th>
            <td><input type="text" id="hero_subtitle" name="hero_subtitle" value="<?php echo esc_attr( $f('hero_subtitle') ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="hero_date">Date Display</label></th>
            <td><input type="text" id="hero_date" name="hero_date" value="<?php echo esc_attr( $f('hero_date') ); ?>" class="regular-text" placeholder="e.g. June 5, 2026 - Stockholm"></td></tr>
        <tr><th><label for="hero_cta_text">CTA Text</label></th>
            <td><input type="text" id="hero_cta_text" name="hero_cta_text" value="<?php echo esc_attr( $f('hero_cta_text') ); ?>" class="regular-text"></td></tr>
        <tr><th><label for="hero_cta_url">CTA URL</label></th>
            <td><input type="url" id="hero_cta_url" name="hero_cta_url" value="<?php echo esc_url( $f('hero_cta_url') ); ?>" class="regular-text"></td></tr>
    </table>
    <?php
}

/* ---------- Save Meta ---------- */

add_action( 'save_post', function ( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // Project
    if ( get_post_type( $post_id ) === 'project' && isset( $_POST['_berghs_project_nonce'] ) && wp_verify_nonce( $_POST['_berghs_project_nonce'], 'berghs_project' ) ) {
        foreach ( [ 'project_company', 'project_background', 'project_solution', 'project_result' ] as $k ) {
            if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, sanitize_text_field( $_POST[ $k ] ) );
        }
        if ( isset( $_POST['project_case_film'] ) ) update_post_meta( $post_id, 'project_case_film', esc_url_raw( $_POST['project_case_film'] ) );
        $members = [];
        if ( isset( $_POST['project_members_name'] ) && is_array( $_POST['project_members_name'] ) ) {
            $names = $_POST['project_members_name'];
            $progs = isset( $_POST['project_members_program'] ) ? $_POST['project_members_program'] : [];
            for ( $i = 0; $i < count( $names ); $i++ ) {
                $name = sanitize_text_field( $names[ $i ] );
                if ( $name ) $members[] = [ 'name' => $name, 'program_class' => sanitize_text_field( $progs[ $i ] ?? '' ) ];
            }
        }
        update_post_meta( $post_id, 'project_group_members', wp_json_encode( $members ) );
        update_post_meta( $post_id, 'project_is_individual', isset( $_POST['project_is_individual'] ) ? '1' : '0' );
    }

    // Schedule
    if ( get_post_type( $post_id ) === 'schedule_item' && isset( $_POST['_berghs_schedule_nonce'] ) && wp_verify_nonce( $_POST['_berghs_schedule_nonce'], 'berghs_schedule' ) ) {
        update_post_meta( $post_id, 'schedule_time', sanitize_text_field( $_POST['schedule_time'] ?? '' ) );
        update_post_meta( $post_id, 'schedule_description', sanitize_textarea_field( $_POST['schedule_description'] ?? '' ) );
        update_post_meta( $post_id, 'schedule_type', sanitize_text_field( $_POST['schedule_type'] ?? '' ) );
        update_post_meta( $post_id, 'schedule_sort_order', intval( $_POST['schedule_sort_order'] ?? 0 ) );
    }

    // Sponsor
    if ( get_post_type( $post_id ) === 'sponsor' && isset( $_POST['_berghs_sponsor_nonce'] ) && wp_verify_nonce( $_POST['_berghs_sponsor_nonce'], 'berghs_sponsor' ) ) {
        update_post_meta( $post_id, 'sponsor_url', esc_url_raw( $_POST['sponsor_url'] ?? '' ) );
    }

    // Hero (front page)
    if ( get_post_type( $post_id ) === 'page' && isset( $_POST['_berghs_hero_nonce'] ) && wp_verify_nonce( $_POST['_berghs_hero_nonce'], 'berghs_hero' ) ) {
        foreach ( [ 'hero_title', 'hero_subtitle', 'hero_date', 'hero_cta_text' ] as $k ) {
            if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, sanitize_text_field( $_POST[ $k ] ) );
        }
        if ( isset( $_POST['hero_cta_url'] ) ) update_post_meta( $post_id, 'hero_cta_url', esc_url_raw( $_POST['hero_cta_url'] ) );
    }
});

/* ---------- Customizer ---------- */

add_action( 'customize_register', function ( $wp_customize ) {

    $wp_customize->add_panel( 'berghs_festival', [ 'title' => 'Festival Settings', 'priority' => 30 ] );

    // Footer & Contact
    $wp_customize->add_section( 'berghs_footer', [ 'title' => 'Footer & Contact', 'panel' => 'berghs_festival' ] );
    $wp_customize->add_setting( 'berghs_footer_address', [ 'default' => "Berghs School of Communication\nSveavägen 56\n111 34 Stockholm", 'sanitize_callback' => 'sanitize_textarea_field' ] );
    $wp_customize->add_control( 'berghs_footer_address', [ 'label' => 'Address', 'section' => 'berghs_footer', 'type' => 'textarea' ] );
    $wp_customize->add_setting( 'berghs_footer_phone', [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'berghs_footer_phone', [ 'label' => 'Phone', 'section' => 'berghs_footer', 'type' => 'text' ] );

    // Social Media
    $wp_customize->add_section( 'berghs_social', [ 'title' => 'Social Media', 'panel' => 'berghs_festival' ] );
    foreach ( [ 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok' ] as $k => $l ) {
        $wp_customize->add_setting( "berghs_social_{$k}", [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( "berghs_social_{$k}", [ 'label' => "{$l} URL", 'section' => 'berghs_social', 'type' => 'url' ] );
    }

    // Ticket CTA
    $wp_customize->add_section( 'berghs_ticket', [ 'title' => 'Ticket CTA', 'panel' => 'berghs_festival' ] );
    $wp_customize->add_setting( 'berghs_ticket_url', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_control( 'berghs_ticket_url', [ 'label' => 'Ticket URL', 'section' => 'berghs_ticket', 'type' => 'url' ] );
    $wp_customize->add_setting( 'berghs_ticket_cta_text', [ 'default' => 'Get Tickets', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'berghs_ticket_cta_text', [ 'label' => 'CTA Button Text', 'section' => 'berghs_ticket', 'type' => 'text' ] );
});

/* ---------- Helpers ---------- */

function berghs_get_video_embed( $url ) {
    if ( ! $url ) return '';
    if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m ) )
        return '<iframe src="https://www.youtube-nocookie.com/embed/' . esc_attr( $m[1] ) . '" frameborder="0" allowfullscreen loading="lazy" title="Case Film"></iframe>';
    if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) )
        return '<iframe src="https://player.vimeo.com/video/' . esc_attr( $m[1] ) . '" frameborder="0" allowfullscreen loading="lazy" title="Case Film"></iframe>';
    return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Watch Case Film</a>';
}

function berghs_get_sponsors() {
    return get_posts( [ 'post_type' => 'sponsor', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
}

function berghs_get_schedule_items() {
    return get_posts( [ 'post_type' => 'schedule_item', 'posts_per_page' => -1, 'meta_key' => 'schedule_sort_order', 'orderby' => 'meta_value_num', 'order' => 'ASC' ] );
}

function berghs_get_project_members( $post_id ) {
    $json = get_post_meta( $post_id, 'project_group_members', true );
    return $json ? ( json_decode( $json, true ) ?: [] ) : [];
}

/* ---------- Flush Rewrite Rules on Activation ---------- */

add_action( 'after_switch_theme', function () {
    do_action( 'init' );
    flush_rewrite_rules();
});

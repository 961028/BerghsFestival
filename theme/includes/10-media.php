<?php

function _app_add_image_sizes() {
	add_image_size( 'project-card', 600, 400, true );
	add_image_size( 'project-hero', 1200, 800, true );
	add_image_size( 'sponsor-logo', 240, 120, false );
}
add_action( 'after_setup_theme', '_app_add_image_sizes' );

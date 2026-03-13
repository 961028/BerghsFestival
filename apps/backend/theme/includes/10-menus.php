<?php

function _app_register_nav_menus() {
	register_nav_menus(
		array(
			'primary' => 'Primary Navigation',
			'footer'  => 'Footer Navigation',
		)
	);
}
add_action( 'after_setup_theme', '_app_register_nav_menus' );

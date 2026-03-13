<?php

function app_resource_img( string $name, array $atts = array() ) {
	$path = __DIR__ . "/../images/{$name}";

	list($width, $height) = getimagesize( $path );

	$url = get_template_directory_uri() . "/images/{$name}";

	$atts['src']       = $url;
	$atts['width']     = $width;
	$atts['height']    = $height;
	$atts['loading'] ??= 'lazy';

	$html = '<img';
	foreach ( $atts as $name => $value ) {
		$html .= " $name=" . '"' . esc_attr( $value ) . '"';
	}
	$html .= '>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function app_attachment_img( mixed $image, string $size = 'full', array $atts = array() ): void {
	if ( is_array( $image ) ) {
		$image = $image['id'];
	}

	if ( ! isset( $atts['sizes'] ) ) {
		$atts['sizes'] = '100vw';
	}

	if ( $image instanceof WP_Post ) {
		$image = $image->ID;
	}

	if ( ! is_numeric( $image ) ) {
		return;
	}

	$image = (int) $image;

	if ( $image <= 0 ) {
		return;
	}

	echo wp_get_attachment_image( $image, $size, false, $atts );
}

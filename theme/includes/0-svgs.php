<?php

use Dom\HTMLDocument;
use Dom\XMLDocument;

function app_svg_img( string $name, array $atts = array() ) {
	$path = __DIR__ . "/../svg/{$name}.svg";

	$content = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	$dom = XMLDocument::createFromString( $content );

	$el = $dom->documentElement; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	$width  = $el->getAttribute( 'width' );
	$height = $el->getAttribute( 'height' );

	$url = get_template_directory_uri() . "/svg/{$name}.svg";

	$atts['src']       = $url;
	$atts['width']     = $width;
	$atts['height']    = $height;
	$atts['loading'] ??= 'lazy';

	$dom = HTMLDocument::createFromString( '<img>', LIBXML_NOERROR );

	$img = $dom->body->firstChild;

	foreach ( $atts as $att_name => $value ) {
		$img->setAttribute( $att_name, $value );
	}

	echo $dom->body->innerHTML; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

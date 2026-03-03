<?php

function app_svg_img( string $name, array $atts = array() ) {
	$path = __DIR__ . "/../svg/{$name}.svg";

	$content = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	$dom = new DOMDocument();
	@$dom->loadXML( $content ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$el = $dom->documentElement; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	$width  = $el->getAttribute( 'width' );
	$height = $el->getAttribute( 'height' );

	$url = get_template_directory_uri() . "/svg/{$name}.svg";

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

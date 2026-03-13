<?php

function app_acf_get_options_key( string $slug ): string {
	return "app-options-$slug";
}

function app_acf_get_options_page_location( string $slug ): array {
	return array(
		array(
			'param'    => 'options_page',
			'operator' => '==',
			'value'    => $slug,
		),
	);
}

function app_acf_get_post_type_key( string $slug ): string {
	return "app-post_type-$slug";
}

function app_acf_get_post_type_location( string $slug ): array {
	return array(
		array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => $slug,
		),
	);
}

function app_acf_get_page_template_key( string $slug ): string {
	return "app-page_template-$slug";
}

function app_acf_get_page_template_location( string $slug ): array {
	return array(
		array(
			'param'    => 'page_template',
			'operator' => '==',
			'value'    => "{$slug}.php",
		),
	);
}

<?php

function app_acf_get_group_key( string ...$slugs ): string {
	static $existing_key_to_slug = array();

	$slug = implode( '-', $slugs );

	$key = 'group_' . md5( $slug );

	$existing_slug = $existing_key_to_slug[ $key ] ?? null;

	if ( null !== $existing_slug && $slug !== $existing_slug ) {
		throw new UnexpectedValueException(
			sprintf(
				'Duplicate group key for slugs "%s" and "%s.',
				esc_html( $existing_slug ),
				esc_html( $slug ),
			)
		);
	}

	$existing_key_to_slug[ $key ] = $slug;

	return $key;
}

function app_acf_get_field_key( string ...$slugs ): string {
	static $existing_key_to_slug = array();

	$slug = implode( '-', $slugs );

	$key = 'field_' . md5( $slug );

	$existing_slug = $existing_key_to_slug[ $key ] ?? null;

	if ( null !== $existing_slug && $slug !== $existing_slug ) {
		throw new UnexpectedValueException(
			sprintf(
				'Duplicate field key for slugs "%s" and "%s.',
				esc_html( $existing_slug ),
				esc_html( $slug ),
			)
		);
	}

	$existing_key_to_slug[ $key ] = $slug;

	return $key;
}

function app_acf_get_options_key_prefix( string $slug ): string {
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

function app_acf_get_post_type_key_prefix( string $slug ): string {
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

function app_acf_get_page_template_key_prefix( string $slug ): string {
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

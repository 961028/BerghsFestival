<?php

declare(strict_types=1);

function _app_filter_template_redirect(): void {
	wp_safe_redirect( admin_url() );
	exit;
}
add_filter( 'template_redirect', '_app_filter_template_redirect' );

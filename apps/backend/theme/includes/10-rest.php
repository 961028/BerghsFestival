<?php

function _app_filter_rest_url_prefix(string $prefix): string
{
    return 'wp/wp-json';
}
add_filter('rest_url_prefix', '_app_filter_rest_url_prefix');

<?php

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * Load .env
 */

Dotenv\Dotenv::createMutable( __DIR__ . '/../../..' )->load();

/*
 * Load .config.php
 */

if ( file_exists( __DIR__ . '/../../../.config.php' ) ) {
	require_once __DIR__ . '/../../../.config.php';
}

/*
 * Environment.
 */

define( 'WP_ENVIRONMENT_TYPE', $_ENV['WP_ENVIRONMENT_TYPE'] ?? 'production' );

/*
 * URLs & Dirs
 */

define( 'WP_HOME', $_ENV['APP_HOME'] . '/wp' );

/*
 * DB
 */

define( 'DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4' );
define( 'DB_COLLATE', $_ENV['DB_COLLATE'] ?? 'utf8mb4_unicode_ci' );
define( 'DB_HOST', $_ENV['DB_HOST'] );
define( 'DB_NAME', $_ENV['DB_NAME'] );
define( 'DB_USER', $_ENV['DB_USER'] );
define( 'DB_PASSWORD', $_ENV['DB_PASSWORD'] );
$table_prefix = $_ENV['DB_TABLE_PREFIX'] ?? 'wp_';

/*
 * Salts
 */

define( 'AUTH_KEY', $_ENV['AUTH_KEY'] );
define( 'SECURE_AUTH_KEY', $_ENV['SECURE_AUTH_KEY'] );
define( 'LOGGED_IN_KEY', $_ENV['LOGGED_IN_KEY'] );
define( 'NONCE_KEY', $_ENV['NONCE_KEY'] );
define( 'AUTH_SALT', $_ENV['AUTH_SALT'] );
define( 'SECURE_AUTH_SALT', $_ENV['SECURE_AUTH_SALT'] );
define( 'LOGGED_IN_SALT', $_ENV['LOGGED_IN_SALT'] );
define( 'NONCE_SALT', $_ENV['NONCE_SALT'] );

/*
 * Misc
 */

define( 'ACF_PRO_LICENSE', $_ENV['ACF_PRO_LICENSE'] );
define( 'DISABLE_WP_CRON', (bool) ( $_ENV['DISABLE_CRON'] ?? false ) );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_CACHE_KEY_SALT', sha1( WP_HOME ) );

/*
 * Load WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}

require_once ABSPATH . 'wp-settings.php';

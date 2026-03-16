<?php

use Faker\Generator;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

require_once __DIR__ . '/../class/class-app-seed-command.php';

WP_CLI::add_command(
	'seed',
	App_Seed_Command::class,
	array( 'shortdesc' => 'Seeds the database with test data.' ),
);

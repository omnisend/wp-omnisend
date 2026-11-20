<?php

$repo_root = dirname( __DIR__, 2 );
$tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : $repo_root . '/.wordpress-develop/current/tests/phpunit';

if ( ! file_exists( $tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test suite not found in {$tests_dir}. Run tests/Integration/bin/install-wp.sh first." . PHP_EOL;
	exit( 1 );
}

require_once __DIR__ . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
require_once $tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () use ( $repo_root ) {
		require $repo_root . '/omnisend/class-omnisend-core-bootstrap.php';
	}
);

// The plugin sets cookies, which fails once any output is sent, so the suite's setup messages go to stderr.
ob_start();
require $tests_dir . '/includes/bootstrap.php';
fwrite( STDERR, ob_get_clean() );

require_once __DIR__ . '/includes/HttpStub.php';
require_once __DIR__ . '/includes/IntegrationTestCase.php';

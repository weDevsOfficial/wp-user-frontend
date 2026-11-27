<?php
/**
 * PHPUnit bootstrap (WordPress test library)
 *
 * Install the library first: composer test:install (a scratch database,
 * dropped and recreated). Set WPUF_TESTS_WITH_PRO=1 to load Pro too.
 *
 * @package WP_User_Frontend
 */

define( 'WPUF_TESTS_PLUGIN_DIR', dirname( __DIR__, 2 ) );

require_once WPUF_TESTS_PLUGIN_DIR . '/vendor/autoload.php';

$wpuf_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wpuf-tests-lib';

if ( ! file_exists( $wpuf_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find {$wpuf_tests_dir}/includes/functions.php." . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo 'Run `composer test:install` first, or set WP_TESTS_DIR.' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit( 1 );
}

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) && file_exists( $wpuf_tests_dir . '/wp-tests-config.php' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', $wpuf_tests_dir . '/wp-tests-config.php' );
}

if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
    define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', WPUF_TESTS_PLUGIN_DIR . '/vendor/yoast/phpunit-polyfills' );
}

require_once $wpuf_tests_dir . '/includes/functions.php';

tests_add_filter(
    'muplugins_loaded',
    function () {
        require WPUF_TESTS_PLUGIN_DIR . '/wpuf.php';

        $pro = dirname( WPUF_TESTS_PLUGIN_DIR ) . '/wpuf-pro/wpuf-pro.php';

        if ( getenv( 'WPUF_TESTS_WITH_PRO' ) && file_exists( $pro ) ) {
            require $pro;
        }
    }
);

require $wpuf_tests_dir . '/includes/bootstrap.php';

require_once __DIR__ . '/src/LegacyFormWriters.php';
require_once __DIR__ . '/src/FormSnapshot.php';
require_once __DIR__ . '/src/LegacySubscriptionWriters.php';
require_once __DIR__ . '/src/LegacySettingsWriters.php';

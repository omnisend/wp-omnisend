<?php
/**
 * WordPress option, REST and plugin stubs for unit tests.
 *
 * @package OmnisendPlugin
 */

function wp_test_reset_options(): void {
	$GLOBALS['wp_test_options']    = array( 'blog_charset' => 'UTF-8' );
	$GLOBALS['wp_test_transients'] = array();
	$GLOBALS['wp_test_redirects']  = array();
	$GLOBALS['wp_test_filters']    = array();

	$GLOBALS['wp_test_woocommerce_plugin_active']    = false;
	$GLOBALS['wp_test_woocommerce_plugin_connected'] = false;
}

wp_test_reset_options();

function get_option( $option = '', $default_value = false ) {
	if ( array_key_exists( $option, $GLOBALS['wp_test_options'] ) ) {
		return $GLOBALS['wp_test_options'][ $option ];
	}

	return $default_value;
}

function update_option( $option, $value ) {
	$GLOBALS['wp_test_options'][ $option ] = $value;

	return true;
}

function delete_option( $option ) {
	unset( $GLOBALS['wp_test_options'][ $option ] );

	return true;
}

function delete_metadata() {
	return true;
}

function current_user_can( $capability ) {
	return true;
}

function wp_verify_nonce( $nonce, $action = -1 ) {
	return ! isset( $GLOBALS['wp_test_nonce_valid'] ) || $GLOBALS['wp_test_nonce_valid'];
}

function wp_create_nonce( $action = -1 ) {
	return 'test-nonce';
}

function wp_nonce_url( $actionurl, $action = -1, $name = '_wpnonce' ) {
	return add_query_arg( $name, wp_create_nonce( $action ), $actionurl );
}

function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {
	return str_repeat( 'a', $length );
}

function admin_url( $path = '' ) {
	return 'https://example.com/wp-admin/' . $path;
}

function add_query_arg( $args, $url = '' ) {
	$parts = explode( '?', $url, 2 );
	$query = array();

	if ( isset( $parts[1] ) ) {
		wp_parse_str( $parts[1], $query );
	}

	$query = array_merge( $query, is_array( $args ) ? $args : array() );

	return $parts[0] . '?' . http_build_query( $query );
}

/**
 * Production code exits after redirecting, so the stub throws instead to let tests assert on the redirect.
 */
class WP_Redirect_Test_Exception extends Exception {}

function wp_safe_redirect( $location, $status = 302 ) {
	$hosts = array( 'example.com' );

	foreach ( $GLOBALS['wp_test_filters']['allowed_redirect_hosts'] ?? array() as $callback ) {
		$hosts = call_user_func( $callback, $hosts );
	}

	$host = wp_parse_url( $location, PHP_URL_HOST );

	if ( $host !== null && ! in_array( $host, $hosts, true ) ) {
		$location = admin_url();
	}

	$GLOBALS['wp_test_redirects'][] = $location;

	throw new WP_Redirect_Test_Exception( $location );
}

function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['wp_test_filters'][ $hook_name ][] = $callback;

	return true;
}

function set_transient( $transient, $value, $expiration = 0 ) {
	$GLOBALS['wp_test_transients'][ $transient ] = $value;

	return true;
}

function get_transient( $transient ) {
	return array_key_exists( $transient, $GLOBALS['wp_test_transients'] ) ? $GLOBALS['wp_test_transients'][ $transient ] : false;
}

function delete_transient( $transient ) {
	unset( $GLOBALS['wp_test_transients'][ $transient ] );

	return true;
}

function rest_ensure_response( $response ) {
	return $response;
}

function wp_next_scheduled( $hook ) {
	return time();
}

function wp_schedule_event( $timestamp, $recurrence, $hook ) {
	return true;
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! defined( 'OMNISEND_CORE_SETTINGS_PAGE' ) ) {
	define( 'OMNISEND_CORE_SETTINGS_PAGE', 'omnisend' );
}

if ( ! defined( 'OMNISEND_CORE_OAUTH_ISSUER' ) ) {
	define( 'OMNISEND_CORE_OAUTH_ISSUER', 'https://app.omnisend.com' );
}

if ( ! defined( 'OMNISEND_CORE_OAUTH_CLIENT_NAME' ) ) {
	define( 'OMNISEND_CORE_OAUTH_CLIENT_NAME', 'wordpress' );
}

if ( ! defined( 'OMNISEND_CORE_PLUGIN_VERSION' ) ) {
	define( 'OMNISEND_CORE_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'OMNISEND_CORE_CRON_SYNC_CONTACT' ) ) {
	define( 'OMNISEND_CORE_CRON_SYNC_CONTACT', 'omnisend_core_sync_contact' );
}

if ( ! defined( 'OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE' ) ) {
	define( 'OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE', 'omnisend_core_every_minute' );
}

if ( ! defined( 'OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION' ) ) {
	define( 'OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION', 'omnisend_api_key' );
}

if ( ! class_exists( 'Omnisend_Core_Bootstrap' ) ) {
	class Omnisend_Core_Bootstrap {

		public static function is_omnisend_woocommerce_plugin_active(): bool {
			return ! empty( $GLOBALS['wp_test_woocommerce_plugin_active'] );
		}

		public static function is_omnisend_woocommerce_plugin_connected(): bool {
			return ! empty( $GLOBALS['wp_test_woocommerce_plugin_connected'] );
		}
	}
}

<?php
/**
 * WordPress option, REST and plugin stubs for unit tests.
 *
 * @package OmnisendPlugin
 */

function wp_test_reset_options(): void {
	$GLOBALS['wp_test_options'] = array( 'blog_charset' => 'UTF-8' );
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

if ( ! defined( 'OMNISEND_CORE_PLUGIN_VERSION' ) ) {
	define( 'OMNISEND_CORE_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'OMNISEND_CORE_CRON_SYNC_CONTACT' ) ) {
	define( 'OMNISEND_CORE_CRON_SYNC_CONTACT', 'omnisend_core_sync_contact' );
}

if ( ! defined( 'OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE' ) ) {
	define( 'OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE', 'omnisend_core_every_minute' );
}

if ( ! class_exists( 'Omnisend_Core_Bootstrap' ) ) {
	class Omnisend_Core_Bootstrap {

		public static function is_omnisend_woocommerce_plugin_active(): bool {
			return false;
		}

		public static function is_omnisend_woocommerce_plugin_connected(): bool {
			return false;
		}
	}
}

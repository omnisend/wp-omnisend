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
	$GLOBALS['wp_test_users']      = array();
	$GLOBALS['wp_test_user_meta']  = array();
	$GLOBALS['wp_test_cron']       = array(
		'next_scheduled' => array(),
		'scheduled'      => array(),
		'cleared'        => array(),
	);

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

function delete_metadata( $meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false ) {
	if ( ! $delete_all ) {
		unset( $GLOBALS['wp_test_user_meta'][ $object_id ][ $meta_key ] );

		return true;
	}

	foreach ( array_keys( $GLOBALS['wp_test_user_meta'] ) as $user_id ) {
		if ( ! array_key_exists( $meta_key, $GLOBALS['wp_test_user_meta'][ $user_id ] ) ) {
			continue;
		}

		if ( '' !== $meta_value && (string) $GLOBALS['wp_test_user_meta'][ $user_id ][ $meta_key ] !== (string) $meta_value ) {
			continue;
		}

		unset( $GLOBALS['wp_test_user_meta'][ $user_id ][ $meta_key ] );
	}

	return true;
}

function delete_user_meta( $user_id, $meta_key ) {
	unset( $GLOBALS['wp_test_user_meta'][ $user_id ][ $meta_key ] );

	return true;
}

if ( ! class_exists( 'WP_User' ) ) {
	class WP_User {
		public $ID;
		public $user_email;

		public function __construct( int $id = 0, string $email = '' ) {
			$this->ID         = $id;
			$this->user_email = $email;
		}
	}
}

function wp_test_add_user( WP_User $user ): void {
	$GLOBALS['wp_test_users'][ $user->ID ] = $user;
}

/**
 * Emulates the parts of WP_User_Query the sync uses: number and the meta_query clause types
 * NOT EXISTS and <= NUMERIC, including nested OR groups.
 */
if ( ! class_exists( 'WP_User_Query' ) ) {
	class WP_User_Query {
		private $args;

		public function __construct( $args = array() ) {
			$this->args = $args;
		}

		public function get_results() {
			$users = $GLOBALS['wp_test_users'];

			if ( ! empty( $this->args['meta_query'] ) ) {
				$users = array_filter(
					$users,
					function ( $user ) {
						return $this->matches_meta_query( $user, $this->args['meta_query'] );
					}
				);
			}

			if ( isset( $this->args['number'] ) ) {
				$users = array_slice( $users, 0, $this->args['number'], true );
			}

			return array_values( $users );
		}

		private function matches_meta_query( $user, $query ) {
			$clauses = array_filter( $query, 'is_int', ARRAY_FILTER_USE_KEY );

			if ( ! empty( $query['relation'] ) && 'OR' === strtoupper( $query['relation'] ) ) {
				foreach ( $clauses as $clause ) {
					if ( $this->matches_clause( $user, $clause ) ) {
						return true;
					}
				}

				return false;
			}

			foreach ( $clauses as $clause ) {
				if ( ! $this->matches_clause( $user, $clause ) ) {
					return false;
				}
			}

			return true;
		}

		private function matches_clause( $user, $clause ) {
			if ( ! empty( $clause['relation'] ) ) {
				return $this->matches_meta_query( $user, $clause );
			}

			$meta   = $GLOBALS['wp_test_user_meta'][ $user->ID ] ?? array();
			$compare = strtoupper( $clause['compare'] ?? '' );

			if ( 'NOT EXISTS' === $compare ) {
				return ! array_key_exists( $clause['key'], $meta );
			}

			if ( ! array_key_exists( $clause['key'], $meta ) ) {
				return false;
			}

			if ( '<=' === $compare ) {
				return (float) $meta[ $clause['key'] ] <= (float) $clause['value'];
			}

			return true;
		}
	}
}

function get_userdata( $user_id ) {
	return $GLOBALS['wp_test_users'][ $user_id ] ?? false;
}

function get_user_meta( $user_id, $key = '', $single = false ) {
	$meta = $GLOBALS['wp_test_user_meta'][ $user_id ] ?? array();

	if ( '' === $key ) {
		return $meta;
	}

	return array_key_exists( $key, $meta ) ? $meta[ $key ] : ( $single ? '' : array() );
}

function update_user_meta( $user_id, $key, $value ) {
	$GLOBALS['wp_test_user_meta'][ $user_id ][ $key ] = $value;

	return true;
}

function wp_clear_scheduled_hook( $hook ) {
	$GLOBALS['wp_test_cron']['cleared'][] = $hook;

	return true;
}

function wp_test_set_next_scheduled( $hook, $timestamp ) {
	$GLOBALS['wp_test_cron']['next_scheduled'][ $hook ] = $timestamp;
}

function home_url() {
	return 'https://example.com';
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
	return add_query_arg( array( $name => wp_create_nonce( $action ) ), $actionurl );
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
	return array_key_exists( $hook, $GLOBALS['wp_test_cron']['next_scheduled'] )
		? $GLOBALS['wp_test_cron']['next_scheduled'][ $hook ]
		: time();
}

function wp_schedule_event( $timestamp, $recurrence, $hook ) {
	$GLOBALS['wp_test_cron']['scheduled'][] = array(
		'timestamp'  => $timestamp,
		'recurrence' => $recurrence,
		'hook'       => $hook,
	);

	return true;
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
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

if ( ! defined( 'OMNISEND_CORE_PLUGIN_NAME' ) ) {
	define( 'OMNISEND_CORE_PLUGIN_NAME', 'Email Marketing by Omnisend' );
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

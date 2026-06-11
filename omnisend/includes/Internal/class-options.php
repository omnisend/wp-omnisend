<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

define( 'NOTIFICATION_NOT_SHOWN', 'shown' );
define( 'NOTIFICATION_DELAYED', 'delayed' );
define( 'NOTIFICATION_DISABLED', 'disabled' );

class Options {

	// omni_send instead of omnisend used to distinct and not interfere with Omnisend for Woo plugin.
	private const OPTION_API_KEY                         = 'omni_send_core_api_key';
	private const OPTION_BRAND_ID                        = 'omni_send_core_brand_id';
	private const OPTION_STORE_CONNECTED                 = 'omni_send_core_store_connected';
	private const OPTION_LANDING_PAGE_VISITED            = 'omni_send_core_landing_page_visited';
	private const OPTION_LANDING_PAGE_VISIT_LAST_TIME    = 'omni_send_core_landing_page_last_visit_time';
	private const OPTION_LANDING_PAGE_NOTIFICATION_STATE = 'omni_send_core_landing_page_notification_state';

	// OAuth options
	private const OPTION_OAUTH_CLIENT_ID                 = 'omni_send_oauth_client_id';
	private const OPTION_OAUTH_CLIENT_SECRET             = 'omni_send_oauth_client_secret';
	private const OPTION_OAUTH_ACCESS_TOKEN              = 'omni_send_oauth_access_token';
	private const OPTION_OAUTH_REFRESH_TOKEN             = 'omni_send_oauth_refresh_token';
	private const OPTION_OAUTH_TOKEN_EXPIRY              = 'omni_send_oauth_token_expiry';

	public static function get_api_key(): string {
		return get_option( self::OPTION_API_KEY, '' );
	}

	public static function get_brand_id(): string {
		$brand_id = get_option( self::OPTION_BRAND_ID );

		return is_string( $brand_id ) ? $brand_id : '';
	}

	public static function set_api_key( $api_key ): bool {
		if ( empty( $api_key ) || ! self::is_api_key_fallback_allowed() ) {
			return false;
		}

		// When setting API key, clear OAuth tokens
		self::clear_oauth_tokens();
		return update_option( self::OPTION_API_KEY, $api_key );
	}

	public static function set_brand_id( $brand_id ): bool {
		if ( ! is_string( $brand_id ) ) {
			return false;
		}

		return update_option( self::OPTION_BRAND_ID, $brand_id );
	}

	public static function set_store_connected(): bool {
		return update_option( self::OPTION_STORE_CONNECTED, true );
	}

	public static function is_store_connected(): bool {
		return boolval( get_option( self::OPTION_STORE_CONNECTED ) );
	}

	public static function is_connected(): bool {
		return self::has_valid_oauth_token() || self::is_api_key_fallback_allowed();
	}

	// OAuth methods
	public static function get_oauth_client_id(): string {
		return get_option( self::OPTION_OAUTH_CLIENT_ID, '' );
	}

	public static function get_oauth_client_secret(): string {
		return get_option( self::OPTION_OAUTH_CLIENT_SECRET, '' );
	}

	public static function get_oauth_access_token(): string {
		return get_option( self::OPTION_OAUTH_ACCESS_TOKEN, '' );
	}

	public static function get_oauth_refresh_token(): string {
		return get_option( self::OPTION_OAUTH_REFRESH_TOKEN, '' );
	}

	public static function get_oauth_token_expiry(): int {
		return get_option( self::OPTION_OAUTH_TOKEN_EXPIRY, 0 );
	}

	public static function set_oauth_credentials( $client_id, $client_secret ): bool {
		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return false;
		}

		update_option( self::OPTION_OAUTH_CLIENT_ID, $client_id );
		update_option( self::OPTION_OAUTH_CLIENT_SECRET, $client_secret );
		return true;
	}

	public static function set_oauth_tokens( $access_token, $refresh_token, $expiry ): bool {
		// When setting OAuth tokens, clear API key for new users
		if ( ! self::is_api_key_fallback_allowed() ) {
			delete_option( self::OPTION_API_KEY );
		}
		
		update_option( self::OPTION_OAUTH_ACCESS_TOKEN, $access_token );
		update_option( self::OPTION_OAUTH_REFRESH_TOKEN, $refresh_token );
		update_option( self::OPTION_OAUTH_TOKEN_EXPIRY, $expiry );
		return true;
	}

	public static function has_valid_oauth_token(): bool {
		$access_token = self::get_oauth_access_token();
		if ( empty( $access_token ) ) {
			return false;
		}

		$expiry = self::get_oauth_token_expiry();
		return $expiry && time() < $expiry;
	}

	public static function get_landing_page_last_visit_time(): int {
		$last_visit_time = get_option( self::OPTION_LANDING_PAGE_VISIT_LAST_TIME );

		return is_numeric( $last_visit_time ) ? intval( $last_visit_time ) : 0;
	}

	public static function get_landing_page_notification_state(): string {
		$notification_state = get_option( self::OPTION_LANDING_PAGE_NOTIFICATION_STATE );
		return is_string( $notification_state ) ? $notification_state : NOTIFICATION_NOT_SHOWN;
	}

	public static function set_landing_page_visited(): void {
		$notification_state = get_option( self::OPTION_LANDING_PAGE_NOTIFICATION_STATE, NOTIFICATION_NOT_SHOWN );
		$last_visit_time    = self::get_landing_page_last_visit_time();
		$current_time       = time();

		if ( $notification_state === NOTIFICATION_NOT_SHOWN ) {
			$notification_state = NOTIFICATION_DELAYED;
		} elseif ( $notification_state === NOTIFICATION_DELAYED && ( $current_time - $last_visit_time ) > self::get_notification_delay_time() ) {
			$notification_state = NOTIFICATION_DISABLED;
		}

		update_option( self::OPTION_LANDING_PAGE_NOTIFICATION_STATE, $notification_state );
		update_option( self::OPTION_LANDING_PAGE_VISIT_LAST_TIME, $current_time );
		update_option( self::OPTION_LANDING_PAGE_VISITED, true );
	}

	public static function get_notification_delay_time(): int {
		return 7 * DAY_IN_SECONDS;
	}

	public static function is_landing_page_visited(): bool {
		return boolval( get_option( self::OPTION_LANDING_PAGE_VISITED ) );
	}

	public static function disconnect(): void {
		// Only clear API key if it's not an existing user
		if ( ! self::is_api_key_fallback_allowed() ) {
			delete_option( self::OPTION_API_KEY );
		}
		self::clear_oauth_tokens();
	}

	public static function delete_all(): void {
		self::disconnect();
	}

	private static function clear_oauth_tokens(): void {
		delete_option( self::OPTION_OAUTH_ACCESS_TOKEN );
		delete_option( self::OPTION_OAUTH_REFRESH_TOKEN );
		delete_option( self::OPTION_OAUTH_TOKEN_EXPIRY );
	}

	public static function is_api_key_fallback_allowed(): bool {
		return ! empty( self::get_api_key() );
	}
}

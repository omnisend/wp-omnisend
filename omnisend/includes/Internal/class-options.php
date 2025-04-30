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
		$api_key = get_option( self::OPTION_API_KEY );

		return is_string( $api_key ) ? $api_key : '';
	}

	public static function get_brand_id(): string {
		$brand_id = get_option( self::OPTION_BRAND_ID );

		return is_string( $brand_id ) ? $brand_id : '';
	}

	public static function set_api_key( $api_key ): bool {
		if ( ! is_string( $api_key ) ) {
			return false;
		}

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
		// Check OAuth first
		if (self::has_valid_oauth_token()) {
			return true;
		}

		// Fall back to API key check
		return self::is_store_connected() && self::get_api_key();
	}

	// OAuth methods
	public static function get_oauth_client_id(): string {
		$client_id = get_option(self::OPTION_OAUTH_CLIENT_ID);
		return is_string($client_id) ? $client_id : '';
	}

	public static function get_oauth_client_secret(): string {
		$client_secret = get_option(self::OPTION_OAUTH_CLIENT_SECRET);
		return is_string($client_secret) ? $client_secret : '';
	}

	public static function get_oauth_access_token(): string {
		$access_token = get_option(self::OPTION_OAUTH_ACCESS_TOKEN);
		return is_string($access_token) ? $access_token : '';
	}

	public static function get_oauth_refresh_token(): string {
		$refresh_token = get_option(self::OPTION_OAUTH_REFRESH_TOKEN);
		return is_string($refresh_token) ? $refresh_token : '';
	}

	public static function get_oauth_token_expiry(): int {
		$expiry = get_option(self::OPTION_OAUTH_TOKEN_EXPIRY);
		return is_numeric($expiry) ? intval($expiry) : 0;
	}

	public static function set_oauth_credentials(array $credentials): bool {
		if (!isset($credentials['client_id']) || !isset($credentials['client_secret'])) {
			return false;
		}

		update_option(self::OPTION_OAUTH_CLIENT_ID, $credentials['client_id']);
		update_option(self::OPTION_OAUTH_CLIENT_SECRET, $credentials['client_secret']);
		return true;
	}

	public static function set_oauth_tokens(array $tokens): bool {
		if (!isset($tokens['access_token']) || !isset($tokens['refresh_token']) || !isset($tokens['expires_in'])) {
			return false;
		}

		update_option(self::OPTION_OAUTH_ACCESS_TOKEN, $tokens['access_token']);
		update_option(self::OPTION_OAUTH_REFRESH_TOKEN, $tokens['refresh_token']);
		update_option(self::OPTION_OAUTH_TOKEN_EXPIRY, time() + $tokens['expires_in']);
		return true;
	}

	public static function has_valid_oauth_token(): bool {
		$access_token = self::get_oauth_access_token();
		$expiry = self::get_oauth_token_expiry();
		return !empty($access_token) && $expiry > time();
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
		// Delete API key and related options
		delete_option( self::OPTION_API_KEY );
		delete_option( self::OPTION_BRAND_ID );
		delete_option( self::OPTION_STORE_CONNECTED );

		// Delete OAuth options
		delete_option( self::OPTION_OAUTH_CLIENT_ID );
		delete_option( self::OPTION_OAUTH_CLIENT_SECRET );
		delete_option( self::OPTION_OAUTH_ACCESS_TOKEN );
		delete_option( self::OPTION_OAUTH_REFRESH_TOKEN );
		delete_option( self::OPTION_OAUTH_TOKEN_EXPIRY );

		// Delete landing page options
		delete_option( self::OPTION_LANDING_PAGE_VISITED );
		delete_option( self::OPTION_LANDING_PAGE_VISIT_LAST_TIME );
		delete_option( self::OPTION_LANDING_PAGE_NOTIFICATION_STATE );
		delete_metadata( 'user', '0', UserMetaData::LAST_SYNC, '', true );
	}

	public static function delete_all(): void {
		self::disconnect();
	}
}

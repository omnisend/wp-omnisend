<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Options {
	// omni_send instead of omnisend used to distinct and not interfere with Omnisend for Woo plugin.
	private const OPTION_API_KEY                      = 'omni_send_core_api_key';
	private const OPTION_BRAND_ID                     = 'omni_send_core_brand_id';
	private const OPTION_STORE_CONNECTED              = 'omni_send_core_store_connected';
	private const OPTION_LANDING_PAGE_VISITED         = 'omni_send_core_landing_page_visited';
	private const OPTION_LANDING_PAGE_VISIT_COUNT     = 'omni_send_core_landing_page_visit_count';
	private const OPTION_LANDING_PAGE_VISIT_LAST_TIME = 'omni_send_core_landing_page_last_visit_time';

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
		return self::is_store_connected() && self::get_api_key();
	}

	public static function get_landing_page_visit_count(): int {
		$visit_count = get_option( self::OPTION_LANDING_PAGE_VISIT_COUNT );

		return is_numeric( $visit_count ) ? intval( $visit_count ) : 0;
	}

	public static function get_landing_page_last_visit_time(): int {
		$last_visit_time = get_option( self::OPTION_LANDING_PAGE_VISIT_LAST_TIME );

		return is_numeric( $last_visit_time ) ? intval( $last_visit_time ) : 0;
	}

	public static function set_landing_page_visited(): bool {
		$visit_count = self::get_landing_page_visit_count();
		update_option( self::OPTION_LANDING_PAGE_VISIT_COUNT, $visit_count + 1 );
		update_option( self::OPTION_LANDING_PAGE_VISIT_LAST_TIME, time() );
		return update_option( self::OPTION_LANDING_PAGE_VISITED, true );
	}

	public static function is_landing_page_visited(): bool {
		return boolval( get_option( self::OPTION_LANDING_PAGE_VISITED ) );
	}

	public static function disconnect(): void {
		delete_option( self::OPTION_API_KEY );
		delete_option( self::OPTION_BRAND_ID );
		delete_option( self::OPTION_STORE_CONNECTED );
		delete_option( self::OPTION_LANDING_PAGE_VISITED );
		delete_metadata( 'user', '0', UserMetaData::LAST_SYNC, '', true );
	}

	public static function delete_all(): void {
		self::disconnect();
	}
}

<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Core_Options {

	private const OPTION_API_KEY = 'omnisend_core_api_key';

	public static function get_api_key(): string {
		$api_key = get_option( self::OPTION_API_KEY );

		return is_string( $api_key ) ? $api_key : '';
	}

	public static function get_brand_id(): string {
		$api_key = get_option( self::OPTION_API_KEY );
		if ( ! is_string( $api_key ) ) {
			return '';
		}

		$exploded = explode( '-', $api_key );

		return count( $exploded ) == 2 ? $exploded[0] : '';
	}

	public static function set_api_key( $api_key ): bool {
		if ( ! is_string( $api_key ) ) {
			return false;
		}

		return update_option( self::OPTION_API_KEY, $api_key );
	}
}

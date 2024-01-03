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

	public static function set_api_key( $api_key ): bool {
		if ( ! is_string( $api_key ) ) {
			return false;
		}

		return update_option( self::OPTION_API_KEY, $api_key );
	}
}

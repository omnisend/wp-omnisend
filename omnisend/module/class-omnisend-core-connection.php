<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Core_Connection {

	public static function display() {
		if ( ! empty( $_POST['action'] ) && 'connect' == $_POST['action'] && ! empty( $_POST['api_key'] ) && ! Omnisend_Core_Options::get_api_key() ) {
			check_admin_referer( 'connect' );
			$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$brand_id = self::get_brand_id( $api_key );
			if ( $brand_id ) {
				Omnisend_Core_Options::set_api_key( $api_key );
				Omnisend_Core_Options::set_brand_id( $brand_id );
			} else {
				echo '<div class="notice notice-error"><p>API key is not valid</p></div>';
			}
		}

		if ( Omnisend_Core_Options::get_api_key() ) {
			echo 'You are connected to Omnisend!';
			return;
		}

		require_once 'view/connection-form.html';
	}

	private static function get_brand_id( $api_key ): string {
		$response = wp_remote_get(
			OMNISEND_CORE_API_V3 . '/accounts',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-API-Key'    => $api_key,
				),
				'timeout' => 10,
			)
		);

		$body = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			return '';
		}

		$arr = json_decode( $body, true );

		return is_array( $arr ) && ! empty( $arr['brandID'] ) && is_string( $arr['brandID'] ) ? $arr['brandID'] : '';
	}
}

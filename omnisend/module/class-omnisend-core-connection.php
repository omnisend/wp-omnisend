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
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$valid   = self::check_api_key( $api_key );
			if ( $valid ) {
				Omnisend_Core_Options::set_api_key( $api_key );
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

	private static function check_api_key( $api_key ): bool {
		$response = wp_remote_get(
			OMNISEND_CORE_API_V3 . '/contacts',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-API-Key'    => $api_key,
				),
				'timeout' => 10,
			)
		);

		$http_code = wp_remote_retrieve_response_code( $response );

		return 200 == $http_code;
	}
}

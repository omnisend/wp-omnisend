<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Core_Connection {

	public static function display() {
		$connected = Omnisend_Core_Options::is_store_connected();

		if ( ! $connected && ! empty( $_POST['action'] ) && 'connect' == $_POST['action'] && ! empty( $_POST['api_key'] ) ) {
			check_admin_referer( 'connect' );
			$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$brand_id = self::get_brand_id( $api_key );

			if ( $brand_id ) {
				// Set credentials so snippet can be added for snippet verification.
				Omnisend_Core_Options::set_api_key( $api_key );
				Omnisend_Core_Options::set_brand_id( $brand_id );

				$connected = self::connect_store( $api_key );
				if ( $connected ) {
					Omnisend_Core_Options::set_store_connected();
				}
			}

			if ( ! $connected ) {
				Omnisend_Core_Options::disconnect(); // Store was not connected, clean up.
				echo '<div class="notice notice-error"><p>API key is not valid.</p></div>';
			}
		}

		if ( $connected ) {
			require_once 'view/connection-success.html';
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

	private static function connect_store( $api_key ): bool {
		$data = array(
			'website'         => site_url(),
			'platform'        => 'wordpress',
			'version'         => OMNISEND_CORE_PLUGIN_VERSION,
			'phpVersion'      => phpversion(),
			'platformVersion' => get_bloginfo( 'version' ),
		);

		$response = wp_remote_post(
			OMNISEND_CORE_API_V3 . '/accounts',
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-API-Key'    => $api_key,
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( $http_code >= 400 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			return false;
		}

		$arr = json_decode( $body, true );

		return ! empty( $arr['verified'] );
	}
}

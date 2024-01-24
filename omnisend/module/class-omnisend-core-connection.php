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


			$contact = new Omnisend_Core_Contact();

			$contact->set_first_name( "Asdf" );
			$contact->set_last_name( 'Doe' );
			$contact->set_email( 'lokalus@lokalus.lt' );
			$contact->set_address( 'Address' );
			$contact->set_city( 'City' );
			$contact->set_state( 'State' );
			$contact->set_country( 'Country' );
			$contact->set_postal_code( 'Postal Code' );
			$contact->set_phone( '12323' );
			$contact->set_birthday( '1990-01-01' );
			$contact->set_welcome_email(true);
			$contact->set_email_opt_in("test-mail");

			$contact->set_email_consent("abc");

			$res = Omnisend_Core_Client::create_contact( $contact );

			/// check if wp error is returned
			if ( is_wp_error( $res ) ) {
				// handle error
				$error_message = $res->get_error_message();
				echo "Something went wrong: $error_message";
			} else {
				// handle success
				echo 'Contact created successfully';
			}


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

	public static function connect_with_omnisend_for_woo_plugin() {
		if ( Omnisend_Core_Options::is_connected() ) {
			return; // Already connected.
		}

		if ( ! Omnisend_Core_Bootstrap::is_omnisend_woocommerce_plugin_active() ) {
			return;
		}

		$api_key = get_option( OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION );
		if ( ! $api_key ) {
			return;
		}

		$brand_id = self::get_brand_id( $api_key );
		if ( ! $brand_id ) {
			return;
		}

		Omnisend_Core_Options::set_api_key( $api_key );
		Omnisend_Core_Options::set_brand_id( $brand_id );
		Omnisend_Core_Options::set_store_connected();
	}
}

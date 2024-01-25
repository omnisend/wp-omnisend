<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use Omnisend\Public\Client\V1\Client;
use Omnisend\Public\Client\V1\Contact;
use Omnisend_Core_Bootstrap;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Connection {

	public static function display(): void {
		$connected = Options::is_store_connected();

		if ( ! $connected && ! empty( $_POST['action'] ) && 'connect' == $_POST['action'] && ! empty( $_POST['api_key'] ) ) {
			check_admin_referer( 'connect' );
			$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$brand_id = self::get_brand_id( $api_key );

			if ( $brand_id ) {
				// Set credentials so snippet can be added for snippet verification.
				Options::set_api_key( $api_key );
				Options::set_brand_id( $brand_id );

				$connected = self::connect_store( $api_key );
				if ( $connected ) {
					Options::set_store_connected();
				}
			}

			if ( ! $connected ) {
				Options::disconnect(); // Store was not connected, clean up.
				echo '<div class="notice notice-error"><p>API key is not valid.</p></div>';
			}
		}

		if ( $connected ) { // todo remove.

			$contact = new Contact();

			$contact->set_first_name( 'Asdf' );
			$contact->set_last_name( 'Doe' );
			$contact->set_email( 'lokalus@lokalus.lt' );
			$contact->set_address( 'Address' );
			$contact->set_city( 'City' );
			$contact->set_state( 'State' );
			$contact->set_country( 'Country' );
			$contact->set_postal_code( 'Postal Code' );
			$contact->set_phone( '12323' );
			$contact->set_birthday( '1990-01-01' );
			$contact->set_welcome_email( true );
			$contact->set_email_opt_in( 'test-mail' );

			$contact->set_email_consent( 'abc' );

			$res = Client::create_contact( $contact );

			// Check if wp error is returned.
			if ( is_wp_error( $res ) ) {
				// handle error.
				$error_message = $res->get_error_message();
				echo "Something went wrong: $error_message"; // phpcs:ignore
			} else {
				// handle success.
				echo 'Contact created successfully';
			}

			require_once __DIR__ . '/../../view/connection-success.html';
			return;
		}

		require_once __DIR__ . '/../../view/connection-success.html';
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

	public static function connect_with_omnisend_for_woo_plugin(): void {
		if ( Options::is_connected() ) {
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

		Options::set_api_key( $api_key );
		Options::set_brand_id( $brand_id );
		Options::set_store_connected();
	}
}

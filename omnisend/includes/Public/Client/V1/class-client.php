<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Public\Client\V1;

use Omnisend\Internal\Options;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );


/**
 * Client class to interact with Omnisend.
 *
 */
class Client {

	/**
	 * Check if plugin is connected to Omnisend account.
	 *
	 * Check and return if plugin connected to Omnisend account. If connection does not exist, it will not be possible
	 * to send data to Omnisend.
	 *
	 * @return bool
	 */
	public static function is_connected(): bool {
		return Options::get_api_key() != '';
	}

	/**
	 * Create contact in Omnisend
	 *
	 * Create a contact in Omnisend. For it to succeed ensure that provided contact at least have email or phone number.
	 *
	 * @param Contact $contact
	 *
	 * @return string|WP_Error
	 */
	public static function create_contact( $contact ): mixed {
		$error = new WP_Error();

		if ( ! $contact instanceof Contact ) {
			$error->add( 'contact', 'Contact is not instance of Omnisend\Public\Client\V1\Contact' );
			return $error;
		}

		if ( ! Options::get_api_key() ) {
			$error->add( 'api_key', 'Omnisend plugin is not connected' );
			return $error;
		}

		$error = $contact->validate();
		if ( $error->has_errors() ) {
			return $error;
		}

		$response = wp_remote_post(
			'https://api.omnisend.com/v3/contacts',
			array(
				'body'    => wp_json_encode( $contact->to_array() ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-API-Key'    => Options::get_api_key(),
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log('wp_remote_post error: ' . $response->get_error_message()); // phpcs:ignore
			return $response;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( $http_code >= 400 ) {
			$body    = wp_remote_retrieve_body( $response );
			$err_msg = "HTTP error: {$http_code} - " . wp_remote_retrieve_response_message( $response ) . " - {$body}";
			error_log($err_msg); // phpcs:ignore
			$error->add( 'omnisend_api', $err_msg );
			return $error;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			$error->add( 'omnisend_api', 'empty response' );
			return $error;
		}

		$arr = json_decode( $body, true );

		if ( empty( $arr['contactID'] ) ) {
			$error->add( 'omnisend_api', 'contactID not found in response' );
			return $error;
		}

		return (string) $arr['contactID'];
	}
}

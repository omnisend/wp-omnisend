<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Internal\V1;

use Omnisend\Sdk\V1\Contact;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Client implements \Omnisend\Sdk\V1\Client {

	private string $api_key;
	private string $plugin_name;
	private string $plugin_version;

	/**
	 * @param string $plugin_name
	 * @param string $plugin_version
	 * @param string $api_key
	 */
	public function __construct( string $api_key, string $plugin_name, string $plugin_version ) {
		$this->api_key        = $api_key;
		$this->plugin_name    = substr( $plugin_name, 0, 50 );
		$this->plugin_version = substr( $plugin_version, 0, 50 );
	}


	public function create_contact( $contact ): mixed {
		$error = new WP_Error();

		if ( $contact instanceof Contact ) {
			$error->merge_from( $contact->validate() );
		} else {
			$error->add( 'contact', 'Contact is not instance of Omnisend\Sdk\V1\Contact.' );
		}

		$error->merge_from( $this->check_setup() );

		if ( $error->has_errors() ) {
			return $error;
		}

		$response = wp_remote_post(
			OMNISEND_CORE_API_V3 . '/contacts',
			array(
				'body'    => wp_json_encode( $contact->to_array() ),
				'headers' => array(
					'Content-Type'          => 'application/json',
					'X-API-Key'             => $this->api_key,
					'X-INTEGRATION-NAME'    => $this->plugin_name,
					'X-INTEGRATION-VERSION' => $this->plugin_version,
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
			$error->add( 'omnisend_api', 'contactID not found in response.' );
			return $error;
		}

		return (string) $arr['contactID'];
	}

	/**
	 * @return WP_Error
	 */
	private function check_setup(): WP_Error {
		$error = new WP_Error();

		if ( ! $this->plugin_name ) {
			$error->add( 'initialisation', 'Client is created with empty plugin name.' );
		}

		if ( ! $this->plugin_version ) {
			$error->add( 'initialisation', 'Client is created with empty plugin version.' );
		}

		if ( ! $this->api_key ) {
			$error->add( 'api_key', 'Omnisend plugin is not connected.' );
		}

		return $error;
	}
}

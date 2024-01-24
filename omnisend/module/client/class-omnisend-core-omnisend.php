<?php

/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnisend_Core_Client {


	public static function create_contact( $contact ): string {

		$response = wp_remote_post(
			'https://api.omnisend.com/v3/contacts',
			array(
				'body'    => wp_json_encode( $contact->to_array() ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-API-Key'    => Omnisend_Core_Options::get_api_key(),
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {

			// TODO throw error
			error_log('wp_remote_post error: ' . $response->get_error_message()); // phpcs:ignore
			return '';
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( $http_code >= 400 ) {
			$body = wp_remote_retrieve_body( $response );
			error_log("HTTP error: {$http_code} - " . wp_remote_retrieve_response_message($response) . " - {$body}"); // phpcs:ignore
			return '';
		}

		$body = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			return '';
		}

		$arr = json_decode( $body, true );

		return ! empty( $arr['contactID'] ) ? $arr['contactID'] : '';
	}
}

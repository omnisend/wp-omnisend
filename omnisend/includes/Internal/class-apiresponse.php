<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class ApiResponse {

	const ERROR_TRANSPORT        = 'omnisend_api_transport';
	const ERROR_UNAUTHORIZED     = 'omnisend_api_unauthorized';
	const ERROR_FORBIDDEN        = 'omnisend_api_forbidden';
	const ERROR_NOT_FOUND        = 'omnisend_api_not_found';
	const ERROR_CONFLICT         = 'omnisend_api_conflict';
	const ERROR_VERSION_RETIRED  = 'omnisend_api_version_retired';
	const ERROR_RATE_LIMITED     = 'omnisend_api_rate_limited';
	const ERROR_SERVER           = 'omnisend_api_server_error';
	const ERROR_HTTP             = 'omnisend_api';
	const ERROR_EMPTY_BODY       = 'omnisend_api_empty_body';
	const ERROR_INVALID_JSON     = 'omnisend_api_invalid_json';
	const ERROR_UNEXPECTED_SHAPE = 'omnisend_api_unexpected_response';

	/**
	 * Turns a wp_remote_* result into a decoded response body.
	 *
	 * @param array|WP_Error $response Result of a wp_remote_* call.
	 * @param bool           $require_body Whether an empty body is an error.
	 *
	 * @return array|WP_Error Decoded body on success, WP_Error describing the transport, status or body failure.
	 */
	public static function parse( $response, bool $require_body = true ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( ! is_numeric( $status ) || (int) $status === 0 ) {
			return self::error( self::ERROR_TRANSPORT, 'Omnisend API request failed: no HTTP status received.' );
		}

		$status = (int) $status;
		$body   = (string) wp_remote_retrieve_body( $response );
		$data   = self::decode( $body );

		if ( $status < 200 || $status > 299 ) {
			return self::status_error( $status, (string) wp_remote_retrieve_response_message( $response ), $data, $body );
		}

		if ( trim( $body ) === '' ) {
			if ( ! $require_body ) {
				return array();
			}

			return self::error( self::ERROR_EMPTY_BODY, "Omnisend API returned HTTP {$status} with an empty body." );
		}

		if ( ! is_array( $data ) ) {
			return self::error( self::ERROR_INVALID_JSON, "Omnisend API returned HTTP {$status} with a body that is not a JSON object." );
		}

		return $data;
	}

	/**
	 * @param string $expected_field Field that the caller expected to find in the response.
	 */
	public static function unexpected_shape_error( string $expected_field ): WP_Error {
		return self::error( self::ERROR_UNEXPECTED_SHAPE, "{$expected_field} not found in response." );
	}

	public static function not_found_error( string $entity ): WP_Error {
		return self::error( self::ERROR_NOT_FOUND, "{$entity} not found." );
	}

	private static function status_error( int $status, string $reason, $data, string $body ): WP_Error {
		$problem = self::problem_details( $data );

		$message = "HTTP error: {$status}";

		if ( $reason !== '' ) {
			$message .= " - {$reason}";
		}

		foreach ( array( 'title', 'detail' ) as $field ) {
			if ( $problem[ $field ] !== '' ) {
				$message .= " - {$problem[ $field ]}";
			}
		}

		if ( $problem['errors'] ) {
			$message .= ' - ' . implode( '; ', $problem['errors'] );
		}

		if ( $problem['retryAfter'] !== null ) {
			$message .= " - retry after {$problem['retryAfter']}s";
		}

		if ( $problem['title'] === '' && $problem['detail'] === '' && ! $problem['errors'] && trim( $body ) !== '' ) {
			$message .= ' - ' . substr( trim( $body ), 0, 500 );
		}

		$error = new WP_Error();
		$error->add( self::error_code_for_status( $status ), $message, $problem );

		return $error;
	}

	/**
	 * Extracts RFC 9457 problem details. Missing fields are normalised, so callers can rely on the shape.
	 */
	private static function problem_details( $data ): array {
		$problem = array(
			'status'     => 0,
			'type'       => '',
			'title'      => '',
			'detail'     => '',
			'instance'   => '',
			'errors'     => array(),
			'retryAfter' => null,
		);

		if ( ! is_array( $data ) ) {
			return $problem;
		}

		if ( isset( $data['status'] ) && is_numeric( $data['status'] ) ) {
			$problem['status'] = (int) $data['status'];
		}

		foreach ( array( 'type', 'title', 'detail', 'instance' ) as $field ) {
			if ( isset( $data[ $field ] ) && is_string( $data[ $field ] ) ) {
				$problem[ $field ] = $data[ $field ];
			}
		}

		if ( isset( $data['retryAfter'] ) && is_numeric( $data['retryAfter'] ) ) {
			$problem['retryAfter'] = (int) $data['retryAfter'];
		}

		if ( ! empty( $data['errors'] ) && is_array( $data['errors'] ) ) {
			foreach ( $data['errors'] as $field_error ) {
				if ( ! is_array( $field_error ) ) {
					continue;
				}

				$field   = isset( $field_error['field'] ) && is_string( $field_error['field'] ) ? $field_error['field'] : '';
				$code    = isset( $field_error['code'] ) && is_string( $field_error['code'] ) ? $field_error['code'] : '';
				$text    = isset( $field_error['message'] ) && is_string( $field_error['message'] ) ? $field_error['message'] : '';
				$details = array_filter( array( $code, $text ) );

				$problem['errors'][] = $field !== '' ? $field . ': ' . implode( ' ', $details ) : implode( ' ', $details );
			}
		}

		return $problem;
	}

	private static function error_code_for_status( int $status ): string {
		switch ( $status ) {
			case 401:
				return self::ERROR_UNAUTHORIZED;
			case 403:
				return self::ERROR_FORBIDDEN;
			case 404:
				return self::ERROR_NOT_FOUND;
			case 409:
				return self::ERROR_CONFLICT;
			case 410:
				return self::ERROR_VERSION_RETIRED;
			case 429:
				return self::ERROR_RATE_LIMITED;
		}

		if ( $status >= 500 ) {
			return self::ERROR_SERVER;
		}

		return self::ERROR_HTTP;
	}

	private static function decode( string $body ) {
		if ( trim( $body ) === '' ) {
			return null;
		}

		return json_decode( $body, true );
	}

	private static function error( string $code, string $message ): WP_Error {
		$error = new WP_Error();
		$error->add( $code, $message );

		return $error;
	}
}

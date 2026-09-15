<?php
/**
 * WordPress HTTP API stubs for unit tests.
 *
 * Responses are queued with WP_Http_Test_Stub::queue() and returned by
 * wp_remote_get()/wp_remote_post() in FIFO order.
 *
 * @package OmnisendPlugin
 */

class WP_Http_Test_Stub {

	/**
	 * @var array<int, array|WP_Error>
	 */
	public static $responses = array();

	/**
	 * @var array<int, array{url: string, args: array, method: string}>
	 */
	public static $requests = array();

	public static function reset(): void {
		self::$responses = array();
		self::$requests  = array();
	}

	/**
	 * @param array|WP_Error $response
	 */
	public static function queue( $response ): void {
		self::$responses[] = $response;
	}

	public static function response( int $status, $body = '', string $message = '' ): array {
		return array(
			'response' => array(
				'code'    => $status,
				'message' => $message,
			),
			'body'     => is_string( $body ) ? $body : json_encode( $body ),
		);
	}

	/**
	 * @return array|WP_Error
	 */
	public static function next( string $url, array $args, string $method ) {
		self::$requests[] = array(
			'url'    => $url,
			'args'   => $args,
			'method' => $method,
		);

		if ( ! self::$responses ) {
			return new WP_Error( 'test_stub', 'No queued response for ' . $method . ' ' . $url );
		}

		return array_shift( self::$responses );
	}

	public static function last_request(): array {
		if ( ! self::$requests ) {
			return array();
		}

		return self::$requests[ count( self::$requests ) - 1 ];
	}
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

function wp_remote_get( $url, $args = array() ) {
	return WP_Http_Test_Stub::next( $url, $args, 'GET' );
}

function wp_remote_post( $url, $args = array() ) {
	return WP_Http_Test_Stub::next( $url, $args, isset( $args['method'] ) ? $args['method'] : 'POST' );
}

function wp_remote_retrieve_response_code( $response ) {
	return isset( $response['response']['code'] ) ? $response['response']['code'] : '';
}

function wp_remote_retrieve_response_message( $response ) {
	return isset( $response['response']['message'] ) ? $response['response']['message'] : '';
}

function wp_remote_retrieve_body( $response ) {
	return isset( $response['body'] ) ? $response['body'] : '';
}

function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

function wp_json_encode( $data ) {
	return json_encode( $data );
}

function site_url() {
	return 'https://example.com';
}

function get_bloginfo( $show = '' ) {
	return '6.4';
}

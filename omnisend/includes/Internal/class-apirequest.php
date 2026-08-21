<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class ApiRequest {

	const VERSION = '2026-03-15';

	/**
	 * Builds the headers every Omnisend API request is made with.
	 *
	 * @param string $authorization Authorization header value built by one of the methods below.
	 * @param array  $extra_headers Headers only some callers send, for example the integration name and version.
	 */
	public static function headers( string $authorization, array $extra_headers = array() ): array {
		return array_merge(
			array(
				'Content-Type'     => 'application/json',
				'Authorization'    => $authorization,
				'Omnisend-Version' => self::VERSION,
			),
			$extra_headers
		);
	}

	/**
	 * Builds the headers of the retained deprecated /v3 account calls, which authenticate with X-API-Key
	 * and are not versioned.
	 *
	 * @param string $api_key Omnisend brand API key.
	 * @param array  $extra_headers Headers only some callers send, for example the integration name and version.
	 */
	public static function legacy_api_key_headers( string $api_key, array $extra_headers = array() ): array {
		return array_merge(
			array(
				'Content-Type' => 'application/json',
				'X-API-Key'    => $api_key,
			),
			$extra_headers
		);
	}

	public static function api_key_authorization( string $api_key ): string {
		return 'Omnisend-API-Key ' . $api_key;
	}

	public static function bearer_authorization( string $access_token ): string {
		return 'Bearer ' . $access_token;
	}

	/**
	 * Resolves the credential the store is connected with. Stores connected with an API key keep using it,
	 * stores connected through OAuth use their access token and never fall back to an API key.
	 *
	 * @param string $api_key API key the caller was created with, empty on OAuth-connected stores.
	 *
	 * @return string|WP_Error Authorization header value, or an error when no usable credential exists.
	 */
	public static function authorization( string $api_key ) {
		if ( Options::get_auth_mode() === Options::AUTH_MODE_OAUTH ) {
			$access_token = OAuthClient::get_valid_access_token();

			if ( is_wp_error( $access_token ) ) {
				return $access_token;
			}

			return self::bearer_authorization( $access_token );
		}

		if ( $api_key === '' ) {
			return new WP_Error( 'api_key', 'Omnisend plugin is not connected.' );
		}

		return self::api_key_authorization( $api_key );
	}
}

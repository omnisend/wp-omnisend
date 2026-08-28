<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class OAuthClient {

	const ERROR_REGISTRATION = 'omnisend_oauth_registration_failed';
	const ERROR_STATE        = 'omnisend_oauth_state_mismatch';
	const ERROR_TOKEN        = 'omnisend_oauth_token_failed';
	const ERROR_REFRESH      = 'omnisend_oauth_refresh_failed';
	const ERROR_NO_TOKEN     = 'omnisend_oauth_no_token';

	const SCOPES = 'brands.read brands.write contacts.read contacts.write events.write products.read products.write';

	private const STATE_TRANSIENT = 'omni_send_core_oauth_state';
	private const STATE_LIFETIME  = 900;

	/**
	 * Access tokens are refreshed slightly before they expire so a request does not fail on a token
	 * that expires while it is in flight.
	 */
	private const EXPIRY_SKEW = 60;

	/**
	 * Starts the connect flow: registers this site as an OAuth client if it has no credentials yet and
	 * returns the URL the administrator is sent to for consent.
	 *
	 * @return string|WP_Error
	 */
	public static function get_authorization_url() {
		if ( Options::get_oauth_client_id() === '' || Options::get_oauth_client_secret() === '' ) {
			$registered = self::register_client();

			if ( is_wp_error( $registered ) ) {
				return $registered;
			}
		}

		$state = wp_generate_password( 32, false );
		set_transient( self::STATE_TRANSIENT, $state, self::STATE_LIFETIME );

		$query = array(
			'response_type' => 'code',
			'client_id'     => Options::get_oauth_client_id(),
			'redirect_uri'  => self::get_redirect_uri(),
			'scope'         => self::SCOPES,
			'state'         => $state,
		);

		// add_query_arg() does not encode values, which would break redirect_uri, as it carries a query string of its own.
		return OMNISEND_CORE_OAUTH_ISSUER . '/oauth2/authorize?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Finishes the connect flow by validating the state and exchanging the authorization code for tokens.
	 *
	 * @return true|WP_Error
	 */
	public static function complete_authorization( string $code, string $state ) {
		$expected_state = get_transient( self::STATE_TRANSIENT );
		delete_transient( self::STATE_TRANSIENT );

		if ( ! is_string( $expected_state ) || $expected_state === '' || ! hash_equals( $expected_state, $state ) ) {
			return new WP_Error( self::ERROR_STATE, 'The Omnisend authorization response did not match this site. Please try connecting again.' );
		}

		if ( $code === '' ) {
			return new WP_Error( self::ERROR_TOKEN, 'Omnisend did not return an authorization code. Please try connecting again.' );
		}

		$tokens = self::request_tokens(
			array(
				'grant_type'   => 'authorization_code',
				'code'         => $code,
				'redirect_uri' => self::get_redirect_uri(),
			)
		);

		if ( is_wp_error( $tokens ) ) {
			return new WP_Error( self::ERROR_TOKEN, $tokens->get_error_message() );
		}

		return self::store_tokens( $tokens, '' );
	}

	/**
	 * @return string|WP_Error Access token usable right now, or an error when the store has to be reconnected.
	 */
	public static function get_valid_access_token() {
		$access_token = Options::get_oauth_access_token();

		if ( $access_token === '' ) {
			return new WP_Error( self::ERROR_NO_TOKEN, 'The store is not connected to Omnisend. Please connect it again.' );
		}

		if ( Options::get_oauth_token_expires_at() > time() + self::EXPIRY_SKEW ) {
			return $access_token;
		}

		$refreshed = self::refresh_tokens();

		if ( is_wp_error( $refreshed ) ) {
			return $refreshed;
		}

		return Options::get_oauth_access_token();
	}

	/**
	 * The consent step rebuilds the authorize request from its parts and does not re-encode redirect_uri, so
	 * everything after the first parameter of the redirect URI is lost. The authorization code is then bound to
	 * the truncated URI and the token request has to send the very same value back, so the redirect URI is kept
	 * to a single parameter and the callback is recognised by the code and state it carries.
	 */
	public static function get_redirect_uri(): string {
		return admin_url( 'admin.php?page=' . OMNISEND_CORE_SETTINGS_PAGE );
	}

	/**
	 * @return true|WP_Error
	 */
	private static function refresh_tokens() {
		$refresh_token = Options::get_oauth_refresh_token();

		if ( $refresh_token === '' ) {
			return new WP_Error( self::ERROR_REFRESH, 'The Omnisend access token expired and this store has no refresh token. Please connect it again.' );
		}

		$tokens = self::request_tokens(
			array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $refresh_token,
			)
		);

		if ( is_wp_error( $tokens ) ) {
			return new WP_Error( self::ERROR_REFRESH, $tokens->get_error_message() );
		}

		return self::store_tokens( $tokens, $refresh_token );
	}

	/**
	 * @return array|WP_Error Token endpoint response.
	 */
	private static function request_tokens( array $grant ) {
		$client_id     = Options::get_oauth_client_id();
		$client_secret = Options::get_oauth_client_secret();

		if ( $client_id === '' || $client_secret === '' ) {
			return new WP_Error( self::ERROR_TOKEN, 'This site is not registered as an Omnisend OAuth client. Please connect it again.' );
		}

		$response = wp_remote_post(
			OMNISEND_CORE_OAUTH_ISSUER . '/oauth2/token',
			array(
				'body'    => array_merge(
					$grant,
					array(
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'timeout' => 10,
			)
		);

		return ApiResponse::parse( $response );
	}

	/**
	 * @param array  $tokens                Token endpoint response.
	 * @param string $current_refresh_token Refresh token to keep when the response does not rotate it.
	 *
	 * @return true|WP_Error
	 */
	private static function store_tokens( array $tokens, string $current_refresh_token ) {
		if ( ! isset( $tokens['access_token'] ) || ! is_string( $tokens['access_token'] ) || $tokens['access_token'] === '' ) {
			return ApiResponse::unexpected_shape_error( 'access_token' );
		}

		$refresh_token = $current_refresh_token;
		if ( isset( $tokens['refresh_token'] ) && is_string( $tokens['refresh_token'] ) && $tokens['refresh_token'] !== '' ) {
			$refresh_token = $tokens['refresh_token'];
		}

		if ( ! isset( $tokens['expires_in'] ) || ! is_numeric( $tokens['expires_in'] ) ) {
			return ApiResponse::unexpected_shape_error( 'expires_in' );
		}

		Options::set_oauth_tokens( $tokens['access_token'], $refresh_token, time() + intval( $tokens['expires_in'] ) );

		return true;
	}

	/**
	 * @return true|WP_Error
	 */
	private static function register_client() {
		$response = wp_remote_post(
			OMNISEND_CORE_OAUTH_ISSUER . '/oauth2/register',
			array(
				'body'    => wp_json_encode(
					array(
						'client_name'   => OMNISEND_CORE_OAUTH_CLIENT_NAME,
						'client_uri'    => site_url(),
						'redirect_uris' => array( self::get_redirect_uri() ),
						'grant_types'   => array( 'authorization_code', 'refresh_token' ),
						'scope'         => self::SCOPES,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 10,
			)
		);

		$registration = ApiResponse::parse( $response );

		if ( is_wp_error( $registration ) ) {
			return self::registration_error( $registration );
		}

		if ( ! isset( $registration['client_id'] ) || ! is_string( $registration['client_id'] ) || $registration['client_id'] === '' ) {
			return self::registration_error( ApiResponse::unexpected_shape_error( 'client_id' ) );
		}

		if ( ! isset( $registration['client_secret'] ) || ! is_string( $registration['client_secret'] ) || $registration['client_secret'] === '' ) {
			return self::registration_error( ApiResponse::unexpected_shape_error( 'client_secret' ) );
		}

		Options::set_oauth_client( $registration['client_id'], $registration['client_secret'] );

		return true;
	}

	private static function registration_error( WP_Error $error ): WP_Error {
		return new WP_Error( self::ERROR_REGISTRATION, 'Registering this site with Omnisend did not go through. Details: ' . $error->get_error_message() );
	}
}

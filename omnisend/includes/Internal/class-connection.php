<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use Omnisend_Core_Bootstrap;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Connection {

	public static $landing_page_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=landing_page';

	private static $signup_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=connect_store';

	// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- WordPress is lowercase as it's required by integration.
	private const WORDPRESS_PLATFORM    = 'wordpress';
	private const OAUTH_NONCE_ACTION    = 'omnisend_oauth_connect';
	private const OAUTH_ERROR_TRANSIENT = 'omni_send_core_oauth_error';

	public static function display(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'omnisend' ) );
		}

		Options::set_landing_page_visited();

		self::display_oauth_error();

		if ( self::show_connected_store_view() ) {
			?>
			<div id="omnisend-connected"></div>
			<?php
			return;
		}

		if ( self::show_connection_view() ) {
			?>
			<script type="text/javascript">
				var plugin_omnisend_signup_url = "<?php echo esc_url_raw( self::get_signup_url() ); ?>";
			</script>
			<div id="omnisend-connection"></div>
			<?php
			return;
		}

		self::resolve_wordpress_settings();

		require_once __DIR__ . '/../../view/landing-page.html';
	}

	/**
	 * Resolve landing page settings from the wordpress-backend Cloudflare Worker.
	 *
	 * This endpoint is intentionally retained because it is served by the
	 * wordpress-backend Cloudflare Worker in the omnisend/wp-omnisend-backend
	 * repository rather than the versioned Omnisend public API, so it is outside
	 * the /api migration.
	 */
	public static function resolve_wordpress_settings(): void {
		$url      = 'https://api.omnisend.com/wordpress/settings?version=' . OMNISEND_CORE_PLUGIN_VERSION;
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( $http_code >= 400 ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return;
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) || ! isset( $data['exploreOmnisendLink'] ) || ! is_string( $data['exploreOmnisendLink'] ) || empty( $data['exploreOmnisendLink'] ) ) {
			return;
		}

		if ( self::get_naked_domain( $data['exploreOmnisendLink'] ) !== 'omnisend.com' ) {
			return;
		}

		self::$landing_page_url = $data['exploreOmnisendLink'];
	}

	/**
	 * @return array|WP_Error Brand data or an error describing the transport, status or body failure.
	 */
	private static function get_account_data( $api_key ) {
		return self::get_brand_data( ApiRequest::api_key_authorization( $api_key ) );
	}

	/**
	 * @param string $authorization Authorization header value of the credential the store is connected with.
	 *
	 * @return array|WP_Error Brand data or an error describing the transport, status or body failure.
	 */
	private static function get_brand_data( string $authorization ) {
		$response = wp_remote_get(
			OMNISEND_CORE_API . '/brands/current',
			array(
				'headers' => ApiRequest::headers( $authorization ),
				'timeout' => 10,
			)
		);

		return ApiResponse::parse( $response );
	}

	/**
	 * Maps API failures to messages an administrator can act on.
	 *
	 * @param string $required_permission Permission the rejected request needed, when known.
	 */
	private static function get_connection_error_message( WP_Error $error, string $required_permission = '' ): string {
		switch ( $error->get_error_code() ) {
			case ApiResponse::ERROR_UNAUTHORIZED:
				return 'The API key was rejected by Omnisend. Check if the API key is correct.';
			case ApiResponse::ERROR_FORBIDDEN:
				return self::get_missing_permission_message( $required_permission );
			case ApiResponse::ERROR_VERSION_RETIRED:
				return 'This Omnisend plugin version uses a retired Omnisend API version. Please update the plugin.';
			case ApiResponse::ERROR_RATE_LIMITED:
				return 'Omnisend is rate limiting requests from this store. Please wait a moment and try again.';
			case ApiResponse::ERROR_SERVER:
				return 'Omnisend service is temporarily unavailable. Please try again later.';
			case ApiResponse::ERROR_TRANSPORT:
			case 'http_request_failed':
				return 'Could not reach Omnisend API from this site. Check your server network or firewall settings. Details: ' . $error->get_error_message();
			case ApiResponse::ERROR_EMPTY_BODY:
			case ApiResponse::ERROR_INVALID_JSON:
			case ApiResponse::ERROR_UNEXPECTED_SHAPE:
				return 'Omnisend API returned an unexpected response. Details: ' . $error->get_error_message();
		}

		return 'The connection did not go through. Details: ' . $error->get_error_message();
	}

	/**
	 * The API gateway rejects the key before Omnisend sees the request, so the failing request has to name the permission it needed.
	 *
	 * @param string $required_permission Permission the rejected request needed, when known.
	 */
	private static function get_missing_permission_message( string $required_permission ): string {
		$permission = $required_permission === '' ? '' : ' (' . $required_permission . ')';

		return 'This API key is missing required permissions' . $permission . '. In Omnisend create a new API key with store connection permissions and paste it here to reconnect.';
	}

	public static function show_connected_store_view(): bool {
		return Options::is_store_connected();
	}

	public static function show_connection_view(): bool {
		$connected = Options::is_store_connected();

		if ( ! $connected && ! empty( $_GET['action'] ) && 'show_connection_form' == $_GET['action'] ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'show_connection_form' ) ) {
				die( 'nonce verification failed: ' . __FILE__ . ':' . __LINE__ );
			}
			return true;
		}

		return false;
	}

	/**
	 * Writes this site into the brand the administrator consented to. The API gateway only allows brand
	 * writes with an OAuth token, so connecting a store always happens on the OAuth callback.
	 *
	 * @param string $authorization Bearer authorization header value.
	 *
	 * @return true|WP_Error True when the store is connected, otherwise an error describing the failure.
	 */
	private static function connect_store( string $authorization ) {
		$data = array(
			'website'         => site_url(),
			'platform'        => self::WORDPRESS_PLATFORM,
			'version'         => OMNISEND_CORE_PLUGIN_VERSION,
			'phpVersion'      => phpversion(),
			'platformVersion' => get_bloginfo( 'version' ),
		);

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/brands/current',
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => ApiRequest::headers( $authorization ),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response, false );

		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		return true;
	}

	/**
	 * Writes this site into the brand the pasted API key belongs to. This is the retained deprecated /v3 call:
	 * brand API keys are rejected by the /api account routes and may not write brand settings at all.
	 *
	 * @return true|WP_Error True when the store is connected, otherwise an error describing the failure.
	 */
	private static function connect_store_with_api_key( string $api_key ) {
		$data = array(
			'website'         => site_url(),
			'platform'        => self::WORDPRESS_PLATFORM,
			'version'         => OMNISEND_CORE_PLUGIN_VERSION,
			'phpVersion'      => phpversion(),
			'platformVersion' => get_bloginfo( 'version' ),
		);

		$response = wp_remote_post(
			OMNISEND_CORE_API_V3 . '/accounts',
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => ApiRequest::legacy_api_key_headers( $api_key ),
				'timeout' => 10,
			)
		);

		$parsed = ApiResponse::parse( $response );

		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		if ( empty( $parsed['verified'] ) ) {
			return ApiResponse::unexpected_value_error( 'verified', 'is not set, so Omnisend did not verify this store' );
		}

		return true;
	}

	/**
	 * Starts and finishes the OAuth connect flow. New connections and reconnections go through it;
	 * stores that are already connected with an API key never reach it.
	 */
	public static function handle_oauth_request(): void {
		$action = isset( $_GET['omnisend_oauth'] ) ? sanitize_text_field( wp_unslash( $_GET['omnisend_oauth'] ) ) : '';

		if ( $action === '' && self::is_oauth_callback() ) {
			$action = 'callback';
		}

		if ( $action !== 'connect' && $action !== 'callback' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $action === 'connect' ) {
			// The callback comes from Omnisend and cannot carry a nonce, so only starting the flow is nonce protected;
			// the callback is verified with the OAuth state instead.
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), self::OAUTH_NONCE_ACTION ) ) {
				self::finish_oauth_request( 'Connecting to Omnisend failed because the request could not be verified. Please try again.' );

				return;
			}

			$authorization_url = OAuthClient::get_authorization_url();

			if ( is_wp_error( $authorization_url ) ) {
				self::finish_oauth_request( self::get_connection_error_message( $authorization_url ) );

				return;
			}

			self::redirect_external( $authorization_url );

			return;
		}

		self::finish_oauth_request( self::complete_oauth_connection() );
	}

	/**
	 * The redirect URI carries no marker of its own, so the consent redirect is recognised by the response it
	 * carries together with a connect attempt waiting for it.
	 */
	private static function is_oauth_callback(): bool {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Omnisend redirects here, so the request is verified with the OAuth state.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( $page !== OMNISEND_CORE_SETTINGS_PAGE || ! OAuthClient::has_pending_state() ) {
			return false;
		}

		return ( ! empty( $_GET['code'] ) && ! empty( $_GET['state'] ) ) || ! empty( $_GET['error'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public static function get_oauth_connect_url(): string {
		return wp_nonce_url(
			admin_url( 'admin.php?page=' . OMNISEND_CORE_SETTINGS_PAGE . '&omnisend_oauth=connect' ),
			self::OAUTH_NONCE_ACTION
		);
	}

	/**
	 * @return string Empty string when the store got connected, otherwise the message to show to the administrator.
	 */
	private static function complete_oauth_connection(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Omnisend redirects here, so the request is verified with the OAuth state below.
		if ( isset( $_GET['error'] ) ) {
			return 'Omnisend did not authorize this store: ' . sanitize_text_field( wp_unslash( $_GET['error'] ) ) . '.';
		}

		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$authorized = OAuthClient::complete_authorization( $code, $state );

		if ( is_wp_error( $authorized ) ) {
			Options::clear_oauth_tokens();

			return self::get_connection_error_message( $authorized );
		}

		$access_token = OAuthClient::get_valid_access_token();

		if ( is_wp_error( $access_token ) ) {
			return self::get_connection_error_message( $access_token );
		}

		$authorization = ApiRequest::bearer_authorization( $access_token );
		$brand         = self::get_brand_data( $authorization );

		if ( is_wp_error( $brand ) ) {
			return self::get_connection_error_message( $brand, 'brands.read' );
		}

		if ( empty( $brand['brandID'] ) || ! is_string( $brand['brandID'] ) ) {
			return self::get_connection_error_message( ApiResponse::unexpected_shape_error( 'brandID' ) );
		}

		if ( ! isset( $brand['platform'] ) || ! is_string( $brand['platform'] ) ) {
			return self::get_connection_error_message( ApiResponse::unexpected_shape_error( 'platform' ) );
		}

		if ( $brand['platform'] !== '' && $brand['platform'] !== self::WORDPRESS_PLATFORM ) {
			Options::clear_oauth_tokens();

			return 'The connection did not go through. This Omnisend account is connected to another platform (' . $brand['platform'] . ').';
		}

		$connected = self::connect_store( $authorization );

		if ( is_wp_error( $connected ) ) {
			// Only the tokens this flow obtained are dropped, so a store that was connected with an API key before keeps working.
			Options::clear_oauth_tokens();

			return self::get_connection_error_message( $connected, 'brands.write' );
		}

		Options::set_brand_id( $brand['brandID'] );
		Options::set_store_connected();
		self::schedule_contact_sync();

		return '';
	}

	private static function finish_oauth_request( string $error_message ): void {
		if ( $error_message !== '' ) {
			set_transient( self::OAUTH_ERROR_TRANSIENT, $error_message, 5 * MINUTE_IN_SECONDS );
		}

		self::redirect( admin_url( 'admin.php?page=' . OMNISEND_CORE_SETTINGS_PAGE ) );
	}

	private static function redirect( string $url ): void {
		wp_safe_redirect( $url );

		exit;
	}

	/**
	 * wp_safe_redirect() drops hosts outside this site, so the consent screen has to be redirected to directly.
	 */
	private static function redirect_external( string $url ): void {
		wp_redirect( $url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Omnisend OAuth issuer, built by the plugin and not from user input.

		exit;
	}

	private static function display_oauth_error(): void {
		$error_message = get_transient( self::OAUTH_ERROR_TRANSIENT );

		if ( ! is_string( $error_message ) || $error_message === '' ) {
			return;
		}

		delete_transient( self::OAUTH_ERROR_TRANSIENT );

		?>
		<div class="notice notice-error omnisend-notice"><p><?php echo esc_html( $error_message ); ?></p></div>
		<?php
	}

	private static function schedule_contact_sync(): void {
		if ( ! wp_next_scheduled( OMNISEND_CORE_CRON_SYNC_CONTACT ) && ! Omnisend_Core_Bootstrap::is_omnisend_woocommerce_plugin_connected() ) {
			wp_schedule_event( time(), OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE, OMNISEND_CORE_CRON_SYNC_CONTACT );
		}
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

		$response = self::get_account_data( $api_key );
		if ( is_wp_error( $response ) || empty( $response['brandID'] ) ) {
			return;
		}

		Options::set_api_key( $api_key );
		Options::set_brand_id( $response['brandID'] );
		Options::set_store_connected();
	}

	/**
	 * Validates the Omnisend signup link after applying filters.
	 *
	 * This function applies the 'omnisend_signup_wp_link' filter,
	 * checks if the resulting URL has the naked domain 'omnisend.com',
	 * and returns the filtered URL if valid, or a default URL otherwise.
	 *
	 * @return string
	 */
	private static function get_signup_url(): string {
		$filtered_url = apply_filters( 'omnisend_signup_wp_link', self::$signup_url );
		$naked_domain = self::get_naked_domain( $filtered_url );

		if ( $naked_domain === 'omnisend.com' ) {
			return $filtered_url;
		}

		return esc_url( self::$signup_url );
	}

	/**
	 * Helper function to extract the naked domain from a URL.
	 *
	 * @param string $url The URL to extract the naked domain from.
	 * @return string|null The naked domain or null if not found.
	 */
	private static function get_naked_domain( string $url ): ?string {
		$parsed_url = wp_parse_url( $url );

		if ( isset( $parsed_url['host'] ) ) {
			$host       = $parsed_url['host'];
			$parts      = explode( '.', $host );
			$part_count = count( $parts );

			if ( $part_count <= 1 ) {
				return $host;
			}

			return $parts[ $part_count - 2 ] . '.' . $parts[ $part_count - 1 ];
		}

		return null;
	}

	/**
	 * Connects a store with an API key its administrator pasted. Brands Omnisend does not know a platform for
	 * are written with the retained deprecated /v3 account call, because API keys may not write brand settings on /api.
	 */
	public static function omnisend_post_connection() {
		$connected = Options::is_store_connected();

		$wordpress_platform = self::WORDPRESS_PLATFORM;

		if ( ! current_user_can( 'manage_options' ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => 'You do not have sufficient permissions to perform this action.',
				)
			);
		}

		if ( ! isset( $_POST['action_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['action_nonce'] ) ), 'connect' ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => 'Nonce verification failed.',
				)
			);
		}

		if ( empty( $_POST['api_key'] ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => 'API key is required.',
				)
			);
		}

		if ( ! $connected && ! empty( $_POST['api_key'] ) ) {
			$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$response = self::get_account_data( $api_key );

			if ( is_wp_error( $response ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => self::get_connection_error_message( $response, 'brands.read' ),
					)
				);
			}

			$brand_id = ! empty( $response['brandID'] ) ? $response['brandID'] : '';

			if ( ! $brand_id ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => 'Omnisend API did not return a brand for this API key. Check if the API key is correct.',
					)
				);
			}

			if ( ! isset( $response['platform'] ) || ! is_string( $response['platform'] ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => self::get_connection_error_message( ApiResponse::unexpected_shape_error( 'platform' ) ),
					)
				);
			}

			if ( isset( $response['connected'] ) && ! is_bool( $response['connected'] ) ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => self::get_connection_error_message( ApiResponse::unexpected_value_error( 'connected', 'is not a boolean' ) ),
					)
				);
			}

			if ( ! empty( $response['connected'] ) && $response['platform'] !== $wordpress_platform ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => 'This Omnisend account is already connected to non-WordPress site. Log in to access it.',
					)
				);
			}

			if ( $response['platform'] === '' ) {
				$connected_store = self::connect_store_with_api_key( $api_key );

				if ( is_wp_error( $connected_store ) ) {
					return rest_ensure_response(
						array(
							'success' => false,
							'error'   => self::get_connection_error_message( $connected_store ),
						)
					);
				}

				Options::set_api_key( $api_key );
				Options::set_brand_id( $brand_id );
				Options::set_store_connected();
				self::schedule_contact_sync();

				return rest_ensure_response(
					array(
						'success' => true,
						'error'   => '',
					)
				);
			}

			if ( $response['platform'] === $wordpress_platform ) {
				Options::set_api_key( $api_key );
				Options::set_brand_id( $brand_id );
				Options::set_store_connected();
				self::schedule_contact_sync();

				return rest_ensure_response(
					array(
						'success' => true,
						'error'   => '',
					)
				);
			}

			Options::disconnect(); // Store was not connected, clean up.
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => 'The connection did not go through. This Omnisend account is connected to another platform (' . $response['platform'] . ').',
				)
			);
		}

		return rest_ensure_response(
			array(
				'success' => false,
				'error'   => 'Something went wrong. Please try again.',
			)
		);
	}
}

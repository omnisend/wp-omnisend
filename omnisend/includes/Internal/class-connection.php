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

	public static function display(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'omnisend' ) );
		}

		Options::set_landing_page_visited();

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

	public static function resolve_wordpress_settings(): void {
		$url      = 'https://api.omnisend.com/wordpress/settings?version=' . OMNISEND_CORE_PLUGIN_VERSION;
		$response = wp_remote_get( $url );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			$data = json_decode( $body, true );
			if ( ! empty( $data['exploreOmnisendLink'] ) ) {
				self::$landing_page_url = $data['exploreOmnisendLink'];
			}
		}
	}

	/**
	 * @return array|WP_Error Brand data or an error describing the transport, status or body failure.
	 */
	private static function get_account_data( $api_key ) {
		$response = wp_remote_get(
			OMNISEND_CORE_API . '/brands/current',
			array(
				'headers' => array(
					'Content-Type'     => 'application/json',
					'Authorization'    => 'Omnisend-API-Key ' . $api_key,
					'Omnisend-Version' => '2026-03-15',
				),
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
	 * @return true|WP_Error True when the store is connected, otherwise an error describing the failure.
	 */
	private static function connect_store( $api_key ) {
		$data = array(
			'website'         => site_url(),
			'platform'        => 'wordpress',
			'version'         => OMNISEND_CORE_PLUGIN_VERSION,
			'phpVersion'      => phpversion(),
			'platformVersion' => get_bloginfo( 'version' ),
		);

		$response = wp_remote_post(
			OMNISEND_CORE_API . '/accounts',
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Content-Type'     => 'application/json',
					'Authorization'    => 'Omnisend-API-Key ' . $api_key,
					'Omnisend-Version' => '2026-03-15',
				),
				'timeout' => 10,
			)
		);

		$arr = ApiResponse::parse( $response );

		if ( is_wp_error( $arr ) ) {
			return $arr;
		}

		if ( empty( $arr['connected'] ) ) {
			return ApiResponse::unexpected_shape_error( 'connected' );
		}

		return true;
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

	public static function omnisend_post_connection() {
		$connected = Options::is_store_connected();

		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText
		$wordpress_platform = 'wordpress'; // WordPress is lowercase as it's required by integration.

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

			if ( ! empty( $response['connected'] ) && $response['platform'] !== $wordpress_platform ) {
				return rest_ensure_response(
					array(
						'success' => false,
						'error'   => 'This Omnisend account is already connected to non-WordPress site. Log in to access it.',
					)
				);
			}

			$connected = false;
			if ( $response['platform'] === $wordpress_platform ) {
				$connected = true;
			}

			if ( $response['platform'] === '' ) {
				$connected = self::connect_store( $api_key );

				if ( is_wp_error( $connected ) ) {
					Options::disconnect(); // Store was not connected, clean up.
					return rest_ensure_response(
						array(
							'success' => false,
							'error'   => self::get_connection_error_message( $connected, 'accounts.write' ),
						)
					);
				}
			}

			if ( $connected ) {
				Options::set_api_key( $api_key );
				Options::set_brand_id( $brand_id );
				Options::set_store_connected();

				if ( ! wp_next_scheduled( OMNISEND_CORE_CRON_SYNC_CONTACT ) && ! Omnisend_Core_Bootstrap::is_omnisend_woocommerce_plugin_connected() ) {
					wp_schedule_event( time(), OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE, OMNISEND_CORE_CRON_SYNC_CONTACT );
				}
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

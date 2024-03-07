<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

use Omnisend_Core_Bootstrap;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Connection {

	public static function display(): void {
		$connected = Options::is_store_connected();
		// phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText
		$wordpress_platform = 'wordpress';

		if ( ! $connected && ! empty( $_POST['action'] ) && 'connect' == $_POST['action'] && ! empty( $_POST['api_key'] ) ) {
			check_admin_referer( 'connect' );
			$api_key  = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
			$response = self::get_account_data( $api_key );
			$brand_id = ! empty( $response['brandID'] ) ? $response['brandID'] : '';

			if ( ! $brand_id ) {
				echo '<div class="notice notice-error"><p>The connection didn’t go through. Check if the API key is correct.</p></div>';
				require_once __DIR__ . '/../../view/connection-form.html';
				return;
			}

			if ( $response['verified'] === true && $response['platform'] !== $wordpress_platform ) {
				echo '<div class="notice notice-error"><p>This Omnisend account is already connected to non-WordPress site. Log in to access it.
				<a target="_blank" href="https://www.omnisend.com/customer-support/">Contact our support</a> if you have other issues.</p></div>';
				require_once __DIR__ . '/../../view/connection-form.html';
				return;
			}

			$connected = false;
			if ( $response['platform'] === $wordpress_platform ) {
				$connected = true;
			}

			if ( $response['platform'] === '' ) {
				$connected = self::connect_store( $api_key );
			}

			if ( $connected ) {
				Options::set_api_key( $api_key );
				Options::set_brand_id( $brand_id );
				Options::set_store_connected();

				if ( ! wp_next_scheduled( OMNISEND_CORE_CRON_SYNC_CONTACT ) && ! Omnisend_Core_Bootstrap::is_omnisend_woocommerce_plugin_connected() ) {
					wp_schedule_event( time(), OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE, OMNISEND_CORE_CRON_SYNC_CONTACT );
				}
			}

			if ( ! $connected ) {
				Options::disconnect(); // Store was not connected, clean up.
				echo '<div class="notice notice-error"><p>The connection didn’t go through. Check if the API key is correct.</p></div>';
			}
		}

		if ( ! $connected ) {
			require_once __DIR__ . '/../../view/landing-page.html';
			return;
		}


		require_once __DIR__ . '/../../view/connection-success.html';
	}

	private static function get_account_data( $api_key ): array {
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

		return is_array( $arr ) ? $arr : array();
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

		$response = self::get_account_data( $api_key );
		if ( ! $response['brandID'] ) {
			return;
		}

		Options::set_api_key( $api_key );
		Options::set_brand_id( $response['brandID'] );
		Options::set_store_connected();
	}
}

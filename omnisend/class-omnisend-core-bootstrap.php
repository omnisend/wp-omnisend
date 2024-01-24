<?php
/**
 * Omnisend plugin
 *
 * Plugin Name: Omnisend
 * Description: Omnisend main plugin that enables integration with Omnisend.
 * Version: 1.0.0
 * Author: Omnisend
 * Author URI: https://www.omnisend.com
 * Developer: Omnisend
 * Developer URI: https://developers.omnisend.com
 * Text Domain: omnisend
 * ------------------------------------------------------------------------
 * Copyright 2024 Omnisend
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

define( 'OMNISEND_CORE_PLUGIN_VERSION', '1.0.0' );
define( 'OMNISEND_CORE_SETTINGS_PAGE', 'omnisend' );
define( 'OMNISEND_CORE_PLUGIN_NAME', 'Omnisend' );
define( 'OMNISEND_CORE_WOOCOMMERCE_PLUGIN_NAME', 'Email Marketing for WooCommerce by Omnisend' );

// Change for different environment.
define( 'OMNISEND_CORE_API_V3', 'https://api.omnisend.com/v3' );
define( 'OMNISEND_CORE_SNIPPET_URL', 'https://omnisnippet1.com/inshop/launcher-v2.js' );

require_once 'module/class-omnisend-core-connection.php';
require_once 'module/class-omnisend-core-options.php';
require_once 'module/class-omnisend-core-snippet.php';

require_once 'module/client/class-omnisend-core-client-contact.php';
require_once 'module/client/class-omnisend-core-omnisend-utils.php';
require_once 'module/client/class-omnisend-core-omnisend.php';

add_action( 'plugins_loaded', 'Omnisend_Core_Bootstrap::load' );
add_action( 'admin_notices', 'Omnisend_Core_Bootstrap::admin_notices' );

class Omnisend_Core_Bootstrap {

	public static function load() {
		if ( self::is_omnisend_woocommerce_plugin_active() || self::is_woocommerce_plugin_activate() ) {
			return; // Do not load if "Omnisend for WooCommerce" or "WooCommerce" plugins are active.
		}

		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) ) {
			if ( in_array( $_GET['page'], array( 'omnisend' ), true ) ) {
				wp_enqueue_style(
					'roboto.css',
					plugin_dir_url( __FILE__ ) . 'module/assets/fonts/roboto/roboto.css?' . time(),
					array(),
					'1.0.0',
				);
				wp_enqueue_style(
					'styles.css',
					plugin_dir_url( __FILE__ ) . 'module/styles/styles.css?' . time(),
					array(),
					'1.0.0',
				);
			}
		}

		add_action( 'admin_menu', 'Omnisend_Core_Bootstrap::add_admin_menu' );
		add_action( 'wp_footer', 'Omnisend_Core_Snippet::add' );
	}

	public static function add_admin_menu() {
		$page_title    = 'Omnisend';
		$menu_title    = 'Omnisend';
		$capability    = 'manage_options';
		$menu_slug     = OMNISEND_CORE_SETTINGS_PAGE;
		$function      = 'Omnisend_Core_Connection::display';
		$omnisend_icon = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0yNi40NDg1IDBINS41NDAyQzIuNDgyMzcgMCAwIDIuNDgxNDkgMCA1LjUzODI0VjI2LjQzOTJDMCAyOS40OTU5IDIuNDgyMzcgMzEuOTc3NCA1LjU0MDIgMzEuOTc3NEg3Ljg3NTg4SDE1LjA2MzVWMjcuMzk4QzE1LjA2MzUgMjIuOTg3NyAxNC41MjE5IDE5LjU5MjUgMTEuMjM4NCAxOC42MjI1VjE1LjAxM0MxNi41NzU1IDE1LjQ4NjggMTkuMjI3MSAxOS40NTcyIDE5LjIyNzEgMjUuOTg4VjMySDIzLjU3MTJIMjYuNDU5OEMyOS41MTc2IDMyIDMyIDI5LjUxODUgMzIgMjYuNDYxOFY1LjUzODI0QzMxLjk4ODcgMi40ODE0OSAyOS41MDYzIDAgMjYuNDQ4NSAwWk0xNi4xNTggMTEuNTUwMkMxNC40NjU0IDExLjU1MDIgMTMuMDg4OSAxMC4xNzQxIDEzLjA4ODkgOC40ODIyQzEzLjA4ODkgNi43OTAyNyAxNC40NjU0IDUuNDE0MTcgMTYuMTU4IDUuNDE0MTdDMTcuODUwNSA1LjQxNDE3IDE5LjIxNTggNi43OTAyNyAxOS4yMTU4IDguNDkzNDhDMTkuMjE1OCAxMC4xODU0IDE3Ljg1MDUgMTEuNTUwMiAxNi4xNTggMTEuNTUwMloiCiAgICAgICAgICBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K';
		$position      = 2;

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $omnisend_icon, $position );
	}

	public static function admin_notices() {
		if ( self::is_omnisend_woocommerce_plugin_active() ) {
			echo '<div class="notice notice-error"><strong>' . esc_html( OMNISEND_CORE_PLUGIN_NAME ) . '</strong> plugin is not compatible with <strong>' . esc_html( OMNISEND_CORE_WOOCOMMERCE_PLUGIN_NAME ) . '</strong> plugin. Please use <strong>' . esc_html( OMNISEND_CORE_WOOCOMMERCE_PLUGIN_NAME ) . '</strong> plugin to connect to Omnisend.</p></div>';
		} elseif ( self::is_woocommerce_plugin_activate() ) {
			echo '<div class="notice notice-error"><strong>WooCommerce</strong> plugin is active. Please use <strong>' . esc_html( OMNISEND_CORE_WOOCOMMERCE_PLUGIN_NAME ) . '</strong> plugin instead of <strong>' . esc_html( OMNISEND_CORE_PLUGIN_NAME ) . '</strong> plugin.</p></div>';
		}
	}

	public static function is_omnisend_woocommerce_plugin_active(): bool {
		return in_array( 'omnisend-connect/omnisend-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	public static function is_woocommerce_plugin_activate() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}

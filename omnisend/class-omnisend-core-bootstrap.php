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

add_action( 'plugins_loaded', 'Omnisend_Core_Bootstrap::load' );
add_action( 'admin_notices', 'Omnisend_Core_Bootstrap::admin_notices' );

class Omnisend_Core_Bootstrap {

	public static function load() {
		add_action( 'admin_menu', 'Omnisend_Core_Bootstrap::add_admin_menu' );
		add_action( 'admin_init', 'Omnisend_Core_Connection::connect_with_omnisend_for_woo_plugin' );

		if ( ! self::is_omnisend_woocommerce_plugin_active() || ! self::is_omnisend_woocommerce_plugin_connected() ) {
			add_action( 'wp_footer', 'Omnisend_Core_Snippet::add' );
		}
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
		if ( Omnisend_Core_Options::is_connected() && self::is_omnisend_woocommerce_plugin_active() && ! get_option( 'omnisend_api_key' ) ) {
			echo '<div class="notice notice-error">If you want to use <strong>Omnisend for Woo</strong> plugin please contact customer support.</p></div>';
		}
	}

	public static function is_omnisend_woocommerce_plugin_active(): bool {
		return in_array( 'omnisend-connect/omnisend-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	public static function is_omnisend_woocommerce_plugin_connected(): bool {
		return self::is_omnisend_woocommerce_plugin_active() && get_option( 'omnisend_account_id', null ) !== null;
	}
}

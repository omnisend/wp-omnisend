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
		add_action( 'admin_init', 'Omnisend_Core_Bootstrap::rename_omnisend_for_woo_menu' );
		add_action( 'admin_init', 'Omnisend_Core_Connection::connect_with_omnisend_for_woo_plugin' );

		if ( self::is_omnisend_woocommerce_plugin_active() && self::is_omnisend_woocommerce_plugin_connected() ) {
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

	public static function rename_omnisend_for_woo_menu() {
		global $menu;

		foreach ( $menu as $k => $props ) {
			if ( empty( $props[0] ) || $props[0] != 'Omnisend' || empty( $props[2] ) || $props[2] != 'omnisend-woocommerce' ) {
				continue;
			}

			$omnisend_woo_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyIiBoZWlnaHQ9IjE5MiIgdmlld0JveD0iMCAwIDE5MiAxOTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xNzAuNiA2OC41OTk5SDE2MC44QzE1OS41IDc3LjI5OTkgMTUyIDgzLjk5OTkgMTQyLjkgODMuOTk5OUgxMDJMMTA2LjQgOTQuNjk5OUwxMTAuMSAxMDMuN0wxMDEuNiA5OC45OTk5TDc0LjYgODMuOTk5OUg2OS40QzY4LjkgODUuODk5OSA2OC41IDg3Ljg5OTkgNjguNSA4OS44OTk5VjE3MC41QzY4LjUgMTgyLjMgNzguMSAxOTEuOSA4OS45IDE5MS45SDk4LjlIMTI2LjZWMTc0LjJDMTI2LjYgMTU3LjIgMTI0LjUgMTQ0LjEgMTExLjggMTQwLjRWMTI2LjVDMTMyLjQgMTI4LjMgMTQyLjYgMTQzLjYgMTQyLjYgMTY4LjhWMTkySDE1OS40SDE3MC41QzE4Mi4zIDE5MiAxOTEuOSAxODIuNCAxOTEuOSAxNzAuNlY4OS44OTk5QzE5MiA3OC4wOTk5IDE4Mi40IDY4LjU5OTkgMTcwLjYgNjguNTk5OVpNMTMwLjkgMTEzLjFDMTI0LjQgMTEzLjEgMTE5LjEgMTA3LjggMTE5LjEgMTAxLjNDMTE5LjEgOTQuNzk5OSAxMjQuNCA4OS40OTk5IDEzMC45IDg5LjQ5OTlDMTM3LjQgODkuNDk5OSAxNDIuNyA5NC43OTk5IDE0Mi43IDEwMS40QzE0Mi43IDEwNy45IDEzNy40IDExMy4xIDEzMC45IDExMy4xWiIgZmlsbD0iIzlBQTFBNyIvPgo8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTEzMy44IDI4Ljk5OTlDMTMxLjUgMjguNTk5OSAxMjkuMiAyOS44OTk5IDEyNy4xIDMyLjk5OTlDMTI1LjUgMzUuMjk5OSAxMjQuMyAzNy44OTk5IDEyMy43IDQwLjU5OTlDMTIzLjMgNDIuMDk5OSAxMjMuMiA0My42OTk5IDEyMy4yIDQ1LjI5OTlDMTIzLjIgNDcuMDk5OSAxMjMuNiA0OS4wOTk5IDEyNC40IDUxLjA5OTlDMTI1LjQgNTMuNTk5OSAxMjYuNyA1NC45OTk5IDEyOC4yIDU1LjI5OTlDMTI5LjggNTUuNTk5OSAxMzEuNSA1NC44OTk5IDEzMy40IDUzLjE5OTlDMTM1LjggNTEuMDk5OSAxMzcuNCA0Ny44OTk5IDEzOC4zIDQzLjY5OTlDMTM4LjUgNDIuMTk5OSAxMzguNyA0MC41OTk5IDEzOC44IDM4Ljk5OTlDMTM4LjggMzcuMTk5OSAxMzguNCAzNS4xOTk5IDEzNy42IDMzLjE5OTlDMTM2LjYgMzAuNjk5OSAxMzUuMyAyOS4yOTk5IDEzMy44IDI4Ljk5OTlaIiBmaWxsPSIjOUFBMUE3Ii8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNOTMuNCAyOC45OTk5QzkxLjEgMjguNTk5OSA4OC44IDI5Ljg5OTkgODYuNyAzMi45OTk5Qzg1LjEgMzUuMjk5OSA4My45IDM3Ljg5OTkgODMuMyA0MC41OTk5QzgyLjkgNDIuMDk5OSA4Mi44IDQzLjY5OTkgODIuOCA0NS4yOTk5QzgyLjggNDcuMDk5OSA4My4yIDQ5LjA5OTkgODQgNTEuMDk5OUM4NSA1My41OTk5IDg2LjMgNTQuOTk5OSA4Ny44IDU1LjI5OTlDODkuNCA1NS41OTk5IDkxLjEgNTQuODk5OSA5MyA1My4xOTk5Qzk1LjQgNTEuMDk5OSA5NyA0Ny44OTk5IDk3LjkgNDMuNjk5OUM5OC4yIDQyLjE5OTkgOTguNCA0MC41OTk5IDk4LjQgMzguOTk5OUM5OC40IDM3LjE5OTkgOTggMzUuMTk5OSA5Ny4yIDMzLjE5OTlDOTYuMiAzMC42OTk5IDk0LjkgMjkuMjk5OSA5My40IDI4Ljk5OTlaIiBmaWxsPSIjOUFBMUE3Ii8+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMTQyLjkgMi41OTk5MUgxNC43QzYuNiAyLjU5OTkxIC0xLjQ3NTIxZS0wNiA5LjE5OTkxIDAuMDk5OTk4NSAxNy4xOTk5VjY1Ljk5OTlDMC4wOTk5OTg1IDc0LjA5OTkgNi43IDgwLjU5OTkgMTQuOCA4MC41OTk5SDc1LjVMMTAzLjIgOTUuOTk5OUw5Ni45IDgwLjU5OTlIMTQyLjlDMTUxIDgwLjU5OTkgMTU3LjYgNzQuMDk5OSAxNTcuNiA2NS45OTk5VjE3LjE5OTlDMTU3LjYgOS4wOTk5MSAxNTEgMi41OTk5MSAxNDIuOSAyLjU5OTkxWk02NC44IDY2Ljg5OTlDNjQuOSA2OC40OTk5IDY0LjcgNjkuODk5OSA2NCA3MS4wOTk5QzYzLjIgNzIuNDk5OSA2Mi4xIDczLjI5OTkgNjAuNiA3My4zOTk5QzU4LjkgNzMuNDk5OSA1Ny4yIDcyLjc5OTkgNTUuNSA3MC45OTk5QzQ5LjUgNjQuODk5OSA0NC43IDU1Ljc5OTkgNDEuMyA0My42OTk5QzM3LjEgNTEuODk5OSAzNCA1OC4wOTk5IDMyIDYyLjE5OTlDMjguMiA2OS4zOTk5IDI1IDczLjE5OTkgMjIuMyA3My4zOTk5QzIwLjYgNzMuNDk5OSAxOS4xIDcyLjA5OTkgMTcuOCA2OC45OTk5QzE0LjUgNjAuNTk5OSAxMSA0NC4zOTk5IDcuMiAyMC4yOTk5QzYuNyAxOC40OTk5IDcuMSAxNi45OTk5IDggMTUuNzk5OUM4LjkgMTQuNTk5OSAxMC4zIDEzLjg5OTkgMTIuMSAxMy43OTk5QzE1LjQgMTMuNDk5OSAxNy4zIDE1LjA5OTkgMTcuNyAxOC4zOTk5QzE5LjcgMzEuNzk5OSAyMS45IDQzLjE5OTkgMjQuMiA1Mi40OTk5TDM4LjMgMjUuNjk5OUMzOS42IDIzLjI5OTkgNDEuMiAyMS45OTk5IDQzLjEgMjEuODk5OUM0NS45IDIxLjY5OTkgNDcuNyAyMy40OTk5IDQ4LjQgMjcuMjk5OUM1MCAzNS43OTk5IDUyLjEgNDMuMDk5OSA1NC41IDQ5LjE5OTlDNTYuMSAzMi45OTk5IDU5IDIxLjE5OTkgNjMgMTMuOTk5OUM2NCAxMi4xOTk5IDY1LjQgMTEuMjk5OSA2Ny4zIDExLjE5OTlDNjguOCAxMS4wOTk5IDcwLjEgMTEuNDk5OSA3MS40IDEyLjQ5OTlDNzIuNiAxMy40OTk5IDczLjMgMTQuNjk5OSA3My40IDE2LjE5OTlDNzMuNSAxNy4zOTk5IDczLjMgMTguMjk5OSA3Mi44IDE5LjI5OTlDNzAuMyAyMy44OTk5IDY4LjIgMzEuNjk5OSA2Ni42IDQyLjQ5OTlDNjUgNTIuOTk5OSA2NC40IDYxLjA5OTkgNjQuOCA2Ni44OTk5Wk0xMDQuMSA1Ni42OTk5QzEwMC4zIDYzLjA5OTkgOTUuMiA2Ni4yOTk5IDg5IDY2LjI5OTlDODcuOSA2Ni4yOTk5IDg2LjcgNjYuMTk5OSA4NS41IDY1Ljg5OTlDODAuOSA2NC45OTk5IDc3LjUgNjIuNDk5OSA3NS4yIDU4LjQ5OTlDNzMuMSA1NC45OTk5IDcyLjEgNTAuNjk5OSA3Mi4xIDQ1Ljc5OTlDNzIuMSAzOS4xOTk5IDczLjggMzMuMTk5OSA3Ny4xIDI3LjY5OTlDODEgMjEuMjk5OSA4NiAxOC4wOTk5IDkyLjIgMTguMDk5OUM5My40IDE4LjA5OTkgOTQuNiAxOC4yOTk5IDk1LjcgMTguNDk5OUMxMDAuMiAxOS4zOTk5IDEwMy43IDIxLjg5OTkgMTA2IDI1Ljg5OTlDMTA4LjEgMjkuMzk5OSAxMDkuMSAzMy40OTk5IDEwOS4xIDM4LjQ5OTlDMTA5LjEgNDUuMTk5OSAxMDcuNCA1MS4xOTk5IDEwNC4xIDU2LjY5OTlaTTE0NC41IDU2LjY5OTlDMTQwLjcgNjMuMDk5OSAxMzUuNiA2Ni4yOTk5IDEyOS40IDY2LjI5OTlDMTI4LjMgNjYuMjk5OSAxMjcuMSA2Ni4xOTk5IDEyNS45IDY1Ljg5OTlDMTIxLjMgNjQuOTk5OSAxMTcuOSA2Mi40OTk5IDExNS42IDU4LjQ5OTlDMTEzLjUgNTQuOTk5OSAxMTIuNSA1MC42OTk5IDExMi41IDQ1Ljc5OTlDMTEyLjUgMzkuMTk5OSAxMTQuMiAzMy4xOTk5IDExNy41IDI3LjY5OTlDMTIxLjQgMjEuMjk5OSAxMjYuNCAxOC4wOTk5IDEzMi42IDE4LjA5OTlDMTMzLjggMTguMDk5OSAxMzUgMTguMjk5OSAxMzYuMSAxOC40OTk5QzE0MC43IDE5LjM5OTkgMTQ0LjEgMjEuODk5OSAxNDYuNCAyNS44OTk5QzE0OC41IDI5LjM5OTkgMTQ5LjUgMzMuNDk5OSAxNDkuNSAzOC40OTk5QzE0OS41IDQ1LjE5OTkgMTQ3LjggNTEuMTk5OSAxNDQuNSA1Ni42OTk5WiIgZmlsbD0iIzlBQTFBNyIvPgo8L3N2Zz4K';

			// Change menu name.
			$menu[ $k ][0] = 'Omnisend for Woo'; // phpcs:ignore
			// Change menu icon.
			$menu[ $k ][6] = $omnisend_woo_icon; // phpcs:ignore
		}
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
		return get_option( 'omnisend_account_id', null ) !== null;
	}
}

<?php
/**
 * Omnisend plugin
 *
 * Plugin Name: Omnisend
 * Description: Omnisend main plugin that enables integration with Omnisend.
 * Version: 1.3.10
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

use Omnisend\Internal\Options;

defined( 'ABSPATH' ) || die( 'no direct access' );

const OMNISEND_CORE_PLUGIN_VERSION = '1.3.10';
const OMNISEND_CORE_SETTINGS_PAGE  = 'omnisend';
const OMNISEND_CORE_PLUGIN_NAME    = 'Email Marketing by Omnisend';

const OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE = 'omni_send_core_every_minute';

const OMNISEND_CORE_CRON_SYNC_CONTACT = 'omni_send_cron_sync_contacts';

// Change for different environment.
const OMNISEND_CORE_API_V3      = 'https://api.omnisend.com/v3';
const OMNISEND_CORE_SNIPPET_URL = 'https://omnisnippet1.com/inshop/launcher-v2.js';

// Omnisend for Woo plugin.
const OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION = 'omnisend_api_key';

spl_autoload_register( array( 'Omnisend_Core_Bootstrap', 'autoloader' ) );
register_uninstall_hook( __FILE__, 'Omnisend_Core_Bootstrap::uninstall' );
add_action( 'plugins_loaded', 'Omnisend_Core_Bootstrap::load' );

class Omnisend_Core_Bootstrap {


	public static function load(): void {
		// phpcs:ignore because linter could not detect internal, but it is fine
		add_filter('cron_schedules', 'Omnisend_Core_Bootstrap::cron_schedules'); // phpcs:ignore

		add_action( 'admin_notices', 'Omnisend_Core_Bootstrap::admin_notices' );
		add_action( 'admin_menu', 'Omnisend_Core_Bootstrap::add_admin_menu' );
		add_action( 'admin_enqueue_scripts', 'Omnisend_Core_Bootstrap::load_omnisend_admin_styles' );

		add_action( 'admin_init', 'Omnisend\Internal\Connection::connect_with_omnisend_for_woo_plugin' );

		if ( ! self::is_omnisend_woocommerce_plugin_active() || ! self::is_omnisend_woocommerce_plugin_connected() ) {
			add_action( 'wp_footer', 'Omnisend\Internal\Snippet::add' );

			add_action( 'user_register', 'Omnisend\Internal\Sync::identify_user_by_id' );
			add_action(
				'wp_login',
				function ( $user_login, $user ) {
					Omnisend\Internal\Sync::identify_user_by_id( $user->ID );
				},
				10,
				2
			);
			add_action( 'profile_update', 'Omnisend\Internal\Sync::identify_user_by_id' );

			add_action( OMNISEND_CORE_CRON_SYNC_CONTACT, 'Omnisend\Internal\Sync::sync_contacts' );
		}
	}

	public static function add_admin_menu() {
		$page_title    = OMNISEND_CORE_PLUGIN_NAME;
		$menu_title    = 'Omnisend' . ( ! Options::is_landing_page_visited() ? ' <span class="update-plugins count-1"><span class="plugin-count">1</span></span>' : '' );
		$capability    = 'manage_options';
		$menu_slug     = OMNISEND_CORE_SETTINGS_PAGE;
		$function      = 'Omnisend\Internal\Connection::display';
		$omnisend_icon = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0yNi40NDg1IDBINS41NDAyQzIuNDgyMzcgMCAwIDIuNDgxNDkgMCA1LjUzODI0VjI2LjQzOTJDMCAyOS40OTU5IDIuNDgyMzcgMzEuOTc3NCA1LjU0MDIgMzEuOTc3NEg3Ljg3NTg4SDE1LjA2MzVWMjcuMzk4QzE1LjA2MzUgMjIuOTg3NyAxNC41MjE5IDE5LjU5MjUgMTEuMjM4NCAxOC42MjI1VjE1LjAxM0MxNi41NzU1IDE1LjQ4NjggMTkuMjI3MSAxOS40NTcyIDE5LjIyNzEgMjUuOTg4VjMySDIzLjU3MTJIMjYuNDU5OEMyOS41MTc2IDMyIDMyIDI5LjUxODUgMzIgMjYuNDYxOFY1LjUzODI0QzMxLjk4ODcgMi40ODE0OSAyOS41MDYzIDAgMjYuNDQ4NSAwWk0xNi4xNTggMTEuNTUwMkMxNC40NjU0IDExLjU1MDIgMTMuMDg4OSAxMC4xNzQxIDEzLjA4ODkgOC40ODIyQzEzLjA4ODkgNi43OTAyNyAxNC40NjU0IDUuNDE0MTcgMTYuMTU4IDUuNDE0MTdDMTcuODUwNSA1LjQxNDE3IDE5LjIxNTggNi43OTAyNyAxOS4yMTU4IDguNDkzNDhDMTkuMjE1OCAxMC4xODU0IDE3Ljg1MDUgMTEuNTUwMiAxNi4xNTggMTEuNTUwMloiCiAgICAgICAgICBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K';
		$position      = 2;

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $omnisend_icon, $position );
	}

	public static function cron_schedules( $schedules ) {
		$schedules[ OMNISEND_CORE_CRON_SCHEDULE_EVERY_MINUTE ] = array(
			'interval' => 60,
			'display'  => __( 'Every minute', 'omnisend' ),
		);

		return $schedules;
	}

	public static function load_omnisend_admin_styles(): void {
		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) ) {
			if ( in_array( $_GET['page'], array( 'omnisend' ), true ) ) {
				wp_enqueue_style(
					'roboto.css',
					plugin_dir_url( __FILE__ ) . 'assets/fonts/roboto/roboto.css?' . time(),
					array(),
					OMNISEND_CORE_PLUGIN_VERSION,
				);
				wp_enqueue_style(
					'styles.css',
					plugin_dir_url( __FILE__ ) . 'styles/styles.css?' . time(),
					array(),
					OMNISEND_CORE_PLUGIN_VERSION,
				);
			}
		}
	}

	public static function admin_notices(): void {
		if ( Options::is_connected() && self::is_omnisend_woocommerce_plugin_active() && ! get_option( OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION ) ) {
			echo '<div class="notice notice-error"><p>If you want to use <strong>Omnisend for Woocommerce</strong> plugin please contact customer support.</p></div>';
		} elseif ( ! Options::is_connected() && ( is_plugin_active( 'woocommerce/woocommerce.php' ) || self::is_omnisend_woocommerce_plugin_active() ) && ! self::is_omnisend_woocommerce_plugin_connected() ) {
			echo '<div class="notice notice-error"><p>If you are using WooCommerce, we strongly recommend starting with the <a href="https://wordpress.org/plugins/omnisend-connect/" target="_blank"><strong>Omnisend for WooCommerce</strong></a> plugin. Install it and follow the instructions.</p></div>';
		}
	}

	public static function is_omnisend_woocommerce_plugin_active(): bool {
		return in_array( 'omnisend-connect/omnisend-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	public static function is_omnisend_woocommerce_plugin_connected(): bool {
		return self::is_omnisend_woocommerce_plugin_active() && get_option( 'omnisend_account_id', null ) !== null;
	}

	/**
	 * Autoloader function to load classes dynamically.
	 *
	 * @param string $class_name The name of the class to load.
	 */
	public static function autoloader( $class_name ): void {
		$namespace = 'Omnisend';

		if ( strpos( $class_name, $namespace ) !== 0 ) {
			return;
		}

		$class       = str_replace( $namespace . '\\', '', $class_name );
		$class_parts = explode( '\\', $class );
		$class_file  = 'class-' . strtolower( array_pop( $class_parts ) ) . '.php';

		$directory = plugin_dir_path( __FILE__ );
		$path      = $directory . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $class_parts ) . DIRECTORY_SEPARATOR . $class_file;

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	public static function uninstall(): void {
		Options::delete_all();
	}
}

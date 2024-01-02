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

define( 'OMNISEND_CORE_SETTINGS_PAGE', 'omnisend' );

add_action( 'admin_menu', 'Omnisend_Core_Bootstrap::add_admin_menu' );

class Omnisend_Core_Bootstrap {

	public static function add_admin_menu() {
		$page_title    = 'Omnisend Core';
		$menu_title    = 'Omnisend Core';
		$capability    = 'manage_options';
		$menu_slug     = OMNISEND_CORE_SETTINGS_PAGE;
		$function      = function () {
			echo 'It works';
		};
		$omnisend_icon = 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzIgMzIiIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0yNi40NDg1IDBINS41NDAyQzIuNDgyMzcgMCAwIDIuNDgxNDkgMCA1LjUzODI0VjI2LjQzOTJDMCAyOS40OTU5IDIuNDgyMzcgMzEuOTc3NCA1LjU0MDIgMzEuOTc3NEg3Ljg3NTg4SDE1LjA2MzVWMjcuMzk4QzE1LjA2MzUgMjIuOTg3NyAxNC41MjE5IDE5LjU5MjUgMTEuMjM4NCAxOC42MjI1VjE1LjAxM0MxNi41NzU1IDE1LjQ4NjggMTkuMjI3MSAxOS40NTcyIDE5LjIyNzEgMjUuOTg4VjMySDIzLjU3MTJIMjYuNDU5OEMyOS41MTc2IDMyIDMyIDI5LjUxODUgMzIgMjYuNDYxOFY1LjUzODI0QzMxLjk4ODcgMi40ODE0OSAyOS41MDYzIDAgMjYuNDQ4NSAwWk0xNi4xNTggMTEuNTUwMkMxNC40NjU0IDExLjU1MDIgMTMuMDg4OSAxMC4xNzQxIDEzLjA4ODkgOC40ODIyQzEzLjA4ODkgNi43OTAyNyAxNC40NjU0IDUuNDE0MTcgMTYuMTU4IDUuNDE0MTdDMTcuODUwNSA1LjQxNDE3IDE5LjIxNTggNi43OTAyNyAxOS4yMTU4IDguNDkzNDhDMTkuMjE1OCAxMC4xODU0IDE3Ljg1MDUgMTEuNTUwMiAxNi4xNTggMTEuNTUwMloiCiAgICAgICAgICBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K';
		$position      = 2;

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $omnisend_icon, $position );
	}
}

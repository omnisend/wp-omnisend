<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

class Omnisend_Core_Snippet {

	public static function add() {
		$brand_id = Omnisend_Core_Options::get_brand_id();
		if ( $brand_id ) {
			require_once 'view/snippet.html';
		}
	}
}

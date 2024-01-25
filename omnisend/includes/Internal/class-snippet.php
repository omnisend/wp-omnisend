<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Snippet {

	public static function add() {
		$brand_id = Omnisend_Core_Options::get_brand_id();
		if ( $brand_id ) {
			require_once 'view/snippet.html';
		}
	}
}

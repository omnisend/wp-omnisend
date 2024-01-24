<?php

/**
 * Omnisend Contact Utils
 *
 * @package OmnisendClient
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Omnisend_Core_Client_Utils {


	public static function is_valid_custom_property_name( $name ): bool {
		return preg_match( '/^[a-zA-Z0-9_]{1,128}$/', $name );
	}

	public static function is_valid_tag( $tag ): bool {
		return preg_match( '/^[a-zA-Z0-9_\- ]{1,128}$/', $tag );
	}

	public static function clean_up_tag( $tag ): string {
		$tag = preg_replace( '/[^A-Za-z0-9_\- ]/', '', $tag );
		return mb_strimwidth( $tag, 0, 128 );
	}

	public static function clean_up_tags( $tags ): array {
		$tags = array_map( 'self::clean_up_tag', $tags );
		$tags = array_filter( $tags, 'self::is_valid_tag' );
		$tags = array_unique( $tags );
		return $tags;
	}
}

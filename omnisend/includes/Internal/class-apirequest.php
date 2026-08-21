<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class ApiRequest {

	const VERSION = '2026-03-15';

	/**
	 * Builds the headers every Omnisend API request is made with.
	 *
	 * @param string $api_key Omnisend brand API key.
	 * @param array  $extra_headers Headers only some callers send, for example the integration name and version.
	 */
	public static function headers( string $api_key, array $extra_headers = array() ): array {
		return array_merge(
			array(
				'Content-Type'     => 'application/json',
				'Authorization'    => 'Omnisend-API-Key ' . $api_key,
				'Omnisend-Version' => self::VERSION,
			),
			$extra_headers
		);
	}
}

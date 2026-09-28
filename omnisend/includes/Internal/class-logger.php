<?php
/**
 * Omnisend plugin
 *
 * @package OmnisendPlugin
 */

namespace Omnisend\Internal;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Logger {

	/**
	 * Writes a message to the PHP error log when debugging is enabled, so support can diagnose sync failures.
	 *
	 * @param string $message What failed.
	 * @param array  $context Extra key and value pairs appended to the message.
	 */
	public static function error( string $message, array $context = array() ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		foreach ( $context as $key => $value ) {
			$message .= ' ' . $key . '=' . $value;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- the only logging channel this plugin has, gated by WP_DEBUG.
		error_log( 'Omnisend: ' . $message );
	}
}

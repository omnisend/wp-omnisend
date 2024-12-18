<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class SendBatchResponse {
	private WP_Error $wp_error;
	private string $batch_id;

	public function __construct( WP_Error $wp_error, string $batch_id = '' ) {
		$this->wp_error = $wp_error;
		$this->batch_id = $batch_id;
	}

	/**
	 * Use to retrieve errors
	 *
	 * @return WP_error $wp_error
	 */
	public function get_wp_error(): WP_Error {
		return $this->wp_error;
	}

	/**
	 * Use to retrieve batch ID
	 *
	 * @return string $batch_id
	 */
	public function get_batch_id(): string {
		return $this->batch_id;
	}
}

<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Public\V1;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

/**
 * Client to interact with Omnisend.
 *
 */
interface Client {

	/**
	 * Create a contact in Omnisend. For it to succeed ensure that provided contact at least have email or phone number.
	 *
	 * @param Contact $contact
	 *
	 * @return string|WP_Error Created/updated contact identifier (ID) or WP_Error
	 */
	public function create_contact( $contact ): mixed;
}

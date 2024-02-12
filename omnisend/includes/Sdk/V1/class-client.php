<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Sdk\V1;

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
	 * @return CreateContactResponse
	 */
	public function create_contact( $contact ): CreateContactResponse;
}

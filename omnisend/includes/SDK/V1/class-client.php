<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

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

	/**
	 * Send customer event to Omnisend. Customer events are used to track customer behavior and trigger automations based on that behavior.
	 *
	 * @param Event $event
	 *
	 * @return SendCustomerEventResponse
	 */
	public function send_customer_event( $event ): SendCustomerEventResponse;
}

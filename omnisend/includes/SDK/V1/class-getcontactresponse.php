<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class GetContactResponse {

	private array $contact_data;

	private bool $email_consent;

	private bool $sms_consent;

	private string $contact_id;

	private WP_Error $wp_error;

    /**
     * @param string $contact_id
     * @param WP_Error $wp_error
     * @param array $contact
     */
	public function __construct( string $contact_id, WP_Error $wp_error, array $contact = array() ) {
		$this->contact_data = $contact;
        $this->sms_consent = false;
        $this->email_consent = false;

        if( isset( $contact['identifiers'] ) ){
            foreach ( $contact['identifiers'] as $single_consent ) {
                if ( isset( $single_consent['channels']['sms']['status'] ) && $single_consent['channels']['sms']['status'] == 'subscribed' ) {
                    $this->sms_consent = true;
                }

                if ( isset( $single_consent['channels']['email']['status'] ) && $single_consent['channels']['email']['status'] == 'subscribed' ) {
                    $this->email_consent = true;
                }
            }
        }

		$this->contact_id = $contact_id;
		$this->wp_error   = $wp_error;
	}

	public function get_contact_id(): string {
		return $this->contact_id;
	}

	public function get_contact(): array {
		return $this->contact_data;
	}

	public function get_email_consent(): int {
		return $this->email_consent;
	}

	public function get_sms_consent(): int {
		return $this->sms_consent;
	}

	public function get_wp_error(): WP_Error {
		return $this->wp_error;
	}
}

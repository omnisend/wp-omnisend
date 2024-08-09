<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use WP_Error;
use Omnisend\Internal\V1\ContactFactory;

defined( 'ABSPATH' ) || die( 'no direct access' );

class GetContactResponse {

	private array $contact;

	private string $contact_id = '';

	private string $first_name = '';

	private string $last_name = '';

	private string $email = '';

	private string $address = '';

	private string $city = '';

	private string $state = '';

	private string $country = '';

	private string $postal_code = '';

	private string $phone = '';

	private string $birthday = '';

	private string $gender = '';

	private string $email_status = '';

	private string $phone_status = '';

	private array $tags = array();

	private array $custom_properties = array();

	private WP_Error $wp_error;

	/**
	 * @param Contact|null $contact
	 * @param WP_Error $wp_error
	 */
	public function __construct( ?Contact $contact, WP_Error $wp_error ) {
		$this->wp_error = $wp_error;

		if ( $contact ) {
			$contact_data  = $contact->to_array();
			$this->contact = $contact_data;

			$this->contact_id        = $contact_data['contactID'] ?? '';
			$this->first_name        = $contact_data['firstName'] ?? '';
			$this->last_name         = $contact_data['lastName'] ?? '';
			$this->email             = $contact_data['email'] ?? '';
			$this->address           = $contact_data['address'] ?? '';
			$this->city              = $contact_data['city'] ?? '';
			$this->state             = $contact_data['state'] ?? '';
			$this->country           = $contact_data['country'] ?? '';
			$this->postal_code       = $contact_data['postalCode'] ?? '';
			$this->phone             = $contact_data['phone'][0] ?? '';
			$this->birthday          = $contact_data['birthdate'] ?? '';
			$this->gender            = $contact_data['gender'] ?? '';
			$this->tags              = $contact_data['tags'] ?? array();
			$this->custom_properties = $contact_data['customProperties'] ?? array();

			if ( isset( $contact_data['identifiers'] ) ) {
				foreach ( $contact_data['identifiers'] as $single_consent ) {
					if ( isset( $single_consent['channels']['sms']['status'] ) ) {
						$this->phone_status = $single_consent['channels']['sms']['status'];
					}
					if ( isset( $single_consent['channels']['email']['status'] ) ) {
						$this->email_status = $single_consent['channels']['email']['status'];
					}
				}
			}
		}
	}

	public function get_contact_id(): string {
		return $this->contact_id;
	}

	public function get_contact(): array {
		return $this->contact;
	}

	public function get_first_name(): string {
		return $this->first_name;
	}

	public function get_last_name(): string {
		return $this->last_name;
	}

	public function get_email(): string {
		return $this->email;
	}

	public function get_phone(): string {
		return $this->phone;
	}

	public function get_address(): string {
		return $this->address;
	}

	public function get_city(): string {
		return $this->city;
	}

	public function get_state(): string {
		return $this->state;
	}

	public function get_country(): string {
		return $this->country;
	}

	public function get_postal_code(): string {
		return $this->postal_code;
	}

	public function get_birthday(): string {
		return $this->birthday;
	}

	public function get_gender(): string {
		return $this->gender;
	}

	public function get_tags(): array {
		return $this->tags;
	}

	public function get_custom_properties(): array {
		return $this->custom_properties;
	}

	public function get_email_status(): string {
		return $this->email_status;
	}

	public function get_phone_status(): string {
		return $this->phone_status;
	}

	public function get_wp_error(): WP_Error {
		return $this->wp_error;
	}
}

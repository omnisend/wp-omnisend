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

	private array $contact;

	private string $contact_id;

	private string $first_name;

	private string $last_name;

	private string $email;

	private string $address;

	private string $city;

	private string $state;

	private string $country;

	private string $postal_code;

	private string $phone;

	private string $birthday;

	private string $gender;

	private string $email_status;

	private string $phone_status;

	private array $tags;

	private array $custom_properties;

	private WP_Error $wp_error;

	/**
	 * @param contact|null $contact $contact $contact
	 * @param WP_Error $wp_error
	 */

	public function __construct( ?contact $contact, WP_Error $wp_error ) {
		$this->wp_error = $wp_error;

		$contact_data  = $contact->to_array();
		$this->contact = $contact_data;

		if ( isset( $contact_data['contactID'] ) ) {
			$this->contact_id = $contact_data['contactID'];
		}

		if ( isset( $contact_data['firstName'] ) ) {
			$this->first_name = $contact_data['firstName'];
		}

		if ( isset( $contact_data['email'] ) ) {
			$this->email = $contact_data['email'];
		}

		if ( isset( $contact_data['lastName'] ) ) {
			$this->last_name = $contact_data['lastName'];
		}

		if ( isset( $contact_data['country'] ) ) {
			$this->country = $contact_data['country'];
		}

		if ( isset( $contact_data['address'] ) ) {
			$this->address = $contact_data['address'];
		}

		if ( isset( $contact_data['city'] ) ) {
			$this->city = $contact_data['city'];
		}

		if ( isset( $contact_data['state'] ) ) {
			$this->state = $contact_data['state'];
		}

		if ( isset( $contact_data['postalCode'] ) ) {
			$this->postal_code = $contact_data['postalCode'];
		}

		if ( isset( $contact_data['phone'] ) ) {
			$this->phone = $contact_data['phone'];
		} else {
			$this->phone = '';
		}

		if ( isset( $contact_data['birthdate'] ) ) {
			$this->birthday = $contact_data['birthdate'];
		}

		if ( isset( $contact_data['gender'] ) ) {
			$this->gender = $contact_data['gender'];
		}

		if ( isset( $contact_data['tags'] ) ) {
			$this->tags = $contact_data['tags'];
		}

		if ( isset( $contact_data['customProperties'] ) ) {
			$this->custom_properties = $contact_data['customProperties'];
		}

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

	public function has_contact_id(): string {
		return $this->contact_id;
	}

	public function get_contact(): array {
		return $this->contact;
	}

	public function has_first_name(): string {
		return $this->first_name;
	}

	public function has_last_name(): string {
		return $this->last_name;
	}

	public function has_email(): string {
		return $this->email;
	}

	public function has_phone(): string {
		return $this->phone;
	}

	public function has_address(): string {
		return $this->address;
	}

	public function has_city(): string {
		return $this->city;
	}

	public function has_state(): string {
		return $this->state;
	}

	public function has_country(): string {
		return $this->country;
	}

	public function has_postal_code(): string {
		return $this->postal_code;
	}

	public function has_birthday(): string {
		return $this->birthday;
	}

	public function has_gender(): string {
		return $this->gender;
	}

	public function has_tags(): array {
		return $this->tags;
	}

	public function has_custom_properties(): array {
		return $this->custom_properties;
	}

	public function has_email_status(): string {
		return $this->email_status;
	}

	public function has_phone_status(): string {
		return $this->phone_status;
	}

	public function get_wp_error(): WP_Error {
		return $this->wp_error;
	}
}

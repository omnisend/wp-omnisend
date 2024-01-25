<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\Public\Client\V1;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

class Contact {
	private $first_name;
	private $last_name;
	private $email;
	private $address;
	private $city;
	private $state;
	private $country;
	private $postal_code;
	private $phone;
	private $birthday;
	private $gender;
	private $send_welcome_email;

	private array $tags = array();
	private ?string $email_consent;
	private ?string $phone_consent;

	private ?string $email_opt_in_source;
	private ?string $phone_opt_in_source;

	private array $custom_properties = array();

	/**
	 * @return WP_Error
	 */
	public function validate(): WP_Error {
		$string_properties = array(
			'first_name',
			'last_name',
			'email',
			'address',
			'city',
			'state',
			'country',
			'postal_code',
			'phone',
			'birthday',
			'gender',
			'email_consent',
			'phone_consent',
			'email_opt_in_source',
			'phone_opt_in_source',
		);

		$error = new WP_Error();

		foreach ( $string_properties as $property ) {
			if ( $this->$property != null && ! is_string( $this->$property ) ) {
				$error->add( $property, 'Not a string.' );
			}
		}

		if ( $this->email != null && ! is_email( $this->email ) && $this->errors['email'] == null ) {
			$error->add( 'email', 'Not a email.' );
		}

		if ( $this->send_welcome_email != null && ! is_bool( $this->send_welcome_email ) ) {
			$error->add( 'send_welcome_email', 'Not a valid boolean.' );
		}

		if ( $this->phone != null && ! is_numeric( $this->phone ) && $this->errors['phone'] == null ) {
			$error->add( 'phone', 'Not a valid phone number.' );
		}

		if ( $this->phone == null && $this->email == null ) {
			$error->add( 'identifier', 'Phone or email must be set.' );
		}

		if ( $this->gender != null && ( $this->gender != 'f' || $this->gender != 'm' ) ) {
			$error->add( 'gender', 'Gender must be "f" or "m".' );
		}

		foreach ( $this->tags as $tag ) {
			if ( ! Utils::is_valid_tag( $tag ) ) {
				$error->add( 'tags', 'Tag "' . $tag . '" is not valid. Please cleanup it before setting it.' );
			}
		}

		foreach ( $this->custom_properties as $custom_property ) {
			if ( ! Utils::is_valid_custom_property_name( $custom_property ) ) {
				$error->add( 'custom_properties', 'Custom property "' . $custom_property . '" is not valid. Please cleanup it before setting it.' );
			}
		}

		return $error;
	}

	public function to_array(): array {
		if ( ! $this->is_valid() ) {
			return array();
		}

		$time_now = gmdate( 'c' );

		$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? 'user agent not found' ) );
		$ip         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'ip not found' ) );

		$email_identifier = array(
			'type'     => 'email',
			'id'       => $this->email,
			'channels' => array(
				'email' => array(
					'status'     => $this->email_opt_in_source || $this->email_consent ? 'subscribed' : 'nonSubscribed',
					'statusDate' => $time_now,
				),
			),
		);
		if ( $this->email_consent ) {
			$email_identifier['consent'] = array(
				'source'    => $this->email_consent,
				'createdAt' => $time_now,
				'ip'        => $ip,
				'userAgent' => $user_agent,
			);
		}

		$arr = array(
			'identifiers' => array(
				$email_identifier,
			),
			'tags'        => $this->tags,
		);

		if ( $this->custom_properties ) {
			$arr['customProperties'] = $this->custom_properties;
		}

		if ( $this->phone ) {
			$phone_identifier = array(
				'type'     => 'phone',
				'id'       => $this->phone,
				'channels' => array(
					'sms' => array(
						'status'     => $this->phone_opt_in_source || $this->phone_consent ? 'subscribed' : 'nonSubscribed',
						'statusDate' => $time_now,
					),
				),
			);
			if ( $this->phone_consent ) {
				$phone_identifier['consent'] = array(
					'source'    => $this->phone_consent,
					'createdAt' => $time_now,
					'ip'        => $ip,
					'userAgent' => $user_agent,
				);
			}
			$arr['identifiers'][] = $phone_identifier;
		}

		if ( $this->first_name ) {
			$arr['firstName'] = $this->first_name;
		}

		if ( $this->last_name ) {
			$arr['lastName'] = $this->last_name;
		}

		if ( $this->address ) {
			$arr['address'] = $this->address;
		}

		if ( $this->city ) {
			$arr['city'] = $this->city;
		}

		if ( $this->state ) {
			$arr['state'] = $this->state;
		}

		if ( $this->country ) {
			$arr['country'] = $this->country;
		}

		if ( $this->postal_code ) {
			$arr['postalCode'] = $this->postal_code;
		}

		if ( $this->birthday ) {
			$arr['birthdate'] = $this->birthday;
		}

		if ( $this->gender ) {
			$arr['gender'] = $this->gender;
		}

		if ( $this->send_welcome_email ) {
			$arr['sendWelcomeEmail'] = $this->send_welcome_email;
		}

		return $arr;
	}


	public function set_email( $email ): void {
		if ( $email && is_string( $email ) ) {
			$this->email = $email;
		}
	}

	public function set_gender( $gender ): void {
		$this->gender = $gender;
	}

	public function set_first_name( $first_name ): void {
		$this->first_name = $first_name;
	}

	public function set_last_name( $last_name ): void {
		$this->last_name = $last_name;
	}

	public function set_address( $address ): void {
		$this->address = $address;
	}

	public function set_city( $city ): void {
		$this->city = $city;
	}

	public function set_state( $state ): void {
		$this->state = $state;
	}

	public function set_country( $country ): void {
		$this->country = $country;
	}

	public function set_postal_code( $postal_code ): void {
		$this->postal_code = $postal_code;
	}

	public function set_phone( $phone ): void {
		$this->phone = $phone;
	}

	public function set_birthday( $birthday ): void {
		$this->birthday = $birthday;
	}

	public function set_welcome_email( $send_welcome_email ): void {
		$this->send_welcome_email = $send_welcome_email;
	}

	public function set_email_opt_in( $opt_in_text ): void {
		$this->email_opt_in_source = $opt_in_text;
	}

	public function set_phone_opt_in( $opt_in_text ): void {
		$this->phone_opt_in_source = $opt_in_text;
	}

	public function set_email_consent( $consent_text ): void {
		$this->email_consent = $consent_text;
	}

	public function set_phone_consent( $consent_text ): void {
		$this->phone_consent = $consent_text;
	}


	/**
	 * @param $key
	 * @param $value
	 * @param bool $clean_up_key clean up key to be compatible with Omnisend
	 *
	 * @return void
	 */
	public function add_custom_property( $key, $value, $clean_up_key = true ): void {
		if ( $clean_up_key ) {
			$key = Utils::clean_up_tag( $key );
		}

		if ( $key == '' ) {
			return;
		}

		$this->custom_properties[ $key ] = $value;
	}

	/**
	 * @param $tag
	 * @param bool $clean_up_tag clean up tag to be compatible with Omnisend
	 *
	 * @return void
	 */
	public function add_tag( $tag, $clean_up_tag = true ): void {
		if ( $clean_up_tag ) {
			$tag = Utils::clean_up_tag( $tag );
		}

		if ( $tag == '' ) {
			return;
		}

		$this->tags[] = $tag;
	}
}

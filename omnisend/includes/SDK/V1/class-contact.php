<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use Omnisend\Internal\Utils;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

/**
 * Omnisend Contact class. It's should be used with Omnisend Client.
 *
 */
class Contact {

	private $id                 = null;
	private $first_name         = null;
	private $last_name          = null;
	private $email              = null;
	private $address            = null;
	private $city               = null;
	private $state              = null;
	private $country            = null;
	private $postal_code        = null;
	private $phone              = null;
	private $birthday           = null;
	private $gender             = null;
	private $send_welcome_email = null;

	private array $tags    = array();
	private $email_consent = null;
	private $phone_consent = null;

	private $email_opt_in_source = null;
	private $phone_opt_in_source = null;

	private array $custom_properties = array();

	/**
	 * Validate contact properties.
	 *
	 * It ensures that phone or email is set and that they are valid. In addition other properties are validated if they are expected type and format.
	 *
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

		if ( $this->email != null && ! is_email( $this->email ) ) {
			$error->add( 'email', 'Not a email.' );
		}

		if ( $this->send_welcome_email != null && ! is_bool( $this->send_welcome_email ) ) {
			$error->add( 'send_welcome_email', 'Not a valid boolean.' );
		}

		if ( $this->phone == null && $this->email == null && $this->id == null ) {
			$error->add( 'identifier', 'Phone, email or ID must be set.' );
		}

		if ( $this->gender != null && ! in_array( $this->gender, array( 'm', 'f' ) ) ) {
			$error->add( 'gender', 'Gender must be "f" or "m".' );
		}

		foreach ( $this->tags as $tag ) {
			if ( ! Utils::is_valid_tag( $tag ) ) {
				$error->add( 'tags', 'Tag "' . $tag . '" is not valid. Please cleanup it before setting it.' );
			}
		}

		foreach ( $this->custom_properties as $custom_property_name => $custom_property_value ) {
			if ( ! Utils::is_valid_custom_property_name( $custom_property_name ) ) {
				$error->add( 'custom_properties', 'Custom property "' . $custom_property_name . '" is not valid. Please cleanup it before setting it.' );
			}
		}

		return $error;
	}

	/**
	 * Convert contact to array.
	 *
	 * If contact is valid it will be transformed to array that can be sent to Omnisend.
	 *
	 * @return array
	 */
	public function to_array(): array {
		if ( $this->validate()->has_errors() ) {
			return array();
		}

		$time_now = gmdate( 'c' );

		$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? 'user agent not found' ) );
		$ip         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'ip not found' ) );

		$arr = array(
			'identifiers' => array(),
			'tags'        => array_values( array_unique( $this->tags ) ),
		);

		if ( $this->email ) {
			$email_identifier = array(
				'type'     => 'email',
				'id'       => $this->email,
				'channels' => array(
					'email' => array(
						'status'     => $this->email_opt_in_source ? 'subscribed' : 'nonSubscribed',
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

			$arr['identifiers'][] = $email_identifier;
		}

		if ( $this->custom_properties ) {
			$arr['customProperties'] = $this->custom_properties;
		}

		if ( $this->phone ) {
			$phone_identifier = array(
				'type'     => 'phone',
				'id'       => $this->phone,
				'channels' => array(
					'sms' => array(
						'status'     => $this->phone_opt_in_source ? 'subscribed' : 'nonSubscribed',
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

		if ( $this->id ) {
			$arr['contactID'] = $this->id;
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


	/**
	 * Convert contact to array for events.
	 *
	 * If contact is valid it will be transformed to array that can be sent to Omnisend while creating event.
	 *
	 * @return array
	 */
	public function to_array_for_event(): array {
		if ( $this->validate()->has_errors() ) {
			return array();
		}

		$time_now = gmdate( 'c' );

		$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? 'user agent not found' ) );
		$ip         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'ip not found' ) );

		$arr = array(
			'consents' => array(),
			'optIns'   => array(),
			'tags'     => array_values( array_unique( $this->tags ) ),
		);

		if ( $this->email ) {
			$arr['email'] = $this->email;

			if ( $this->email_consent ) {
				$email_cahnnel_consent = array(
					'channel'   => 'email',
					'source'    => $this->email_consent,
					'createdAt' => $time_now,
					'ip'        => $ip,
					'userAgent' => $user_agent,
				);

				$arr['consents'][] = $email_cahnnel_consent;
			}

			if ( $this->email_opt_in_source ) {
				$email_cahnnel_opt_in = array(
					'channel'   => 'email',
					'createdAt' => $time_now,
					'source'    => $this->email_opt_in_source,
				);

				$arr['optIns'][] = $email_cahnnel_opt_in;
			}
		}

		if ( $this->custom_properties ) {
			$arr['customProperties'] = $this->custom_properties;
		}

		if ( $this->phone ) {
			$arr['phone'] = $this->phone;

			if ( $this->phone_consent ) {
				$phone_channel_consent = array(
					'channel'   => 'phone',
					'source'    => $this->phone_consent,
					'createdAt' => $time_now,
					'ip'        => $ip,
					'userAgent' => $user_agent,
				);
				$arr['consents'][]     = $phone_channel_consent;
			}

			if ( $this->phone_opt_in_source ) {
				$phone_cahnnel_opt_in = array(
					'channel'   => 'phone',
					'createdAt' => $time_now,
					'source'    => $this->phone_opt_in_source,
				);

				$arr['optIns'][] = $phone_cahnnel_opt_in;
			}
		}

		if ( $this->id ) {
			$arr['id'] = $this->id;
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

		return $arr;
	}


	/**
	 * Sets contact email.
	 *
	 * @param $email
	 *
	 * @return void
	 */
	public function set_email( $email ): void {
		if ( $email && is_string( $email ) ) {
			$this->email = $email;
		}
	}

	/**
	 * Sets contact id.
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function set_id( $id ): void {
		if ( $id && is_string( $id ) ) {
			$this->id = $id;
		}
	}

	/**
	 * Sets contact gender. It can be "m" or "f".
	 *
	 * @param $gender
	 *
	 * @return void
	 */
	public function set_gender( $gender ): void {
		$this->gender = $gender;
	}

	/**
	 * Sets contact first_name.
	 *
	 * @param $first_name
	 *
	 * @return void
	 */
	public function set_first_name( $first_name ): void {
		$this->first_name = $first_name;
	}

	/**
	 * Sets contact last_name.
	 *
	 * @param $last_name
	 *
	 * @return void
	 */
	public function set_last_name( $last_name ): void {
		$this->last_name = $last_name;
	}

	/**
	 * Sets contact address.
	 *
	 * It's expected that it will be a street address. Other address parts can be set with other methods.
	 *
	 * @param $address
	 *
	 * @return void
	 */
	public function set_address( $address ): void {
		$this->address = $address;
	}

	/**
	 * Sets contact city.
	 *
	 *
	 * @param $city
	 *
	 * @return void
	 */
	public function set_city( $city ): void {
		$this->city = $city;
	}

	/**
	 * Sets contact state.
	 *
	 *
	 * @param $state
	 *
	 * @return void
	 */
	public function set_state( $state ): void {
		$this->state = $state;
	}

	/**
	 * Sets contact country.
	 *
	 *
	 * @param $country
	 *
	 * @return void
	 */
	public function set_country( $country ): void {
		$this->country = $country;
	}

	/**
	 * Sets contact postal_code.
	 *
	 * @param $postal_code
	 *
	 * @return void
	 */
	public function set_postal_code( $postal_code ): void {
		$this->postal_code = $postal_code;
	}

	/**
	 * Sets contact set_phone.
	 *
	 * @param $set_phone
	 *
	 * @return void
	 */
	public function set_phone( $phone ): void {
		$this->phone = $phone;
	}

	/**
	 * Sets contact birthday. It should be in format "YYYY-MM-DD".
	 *
	 * @param $birthday
	 *
	 * @return void
	 */
	public function set_birthday( $birthday ): void {
		$this->birthday = $birthday;
	}

	/**
	 * If set to true and Welcome Email automation is enabled, then Omnisend will send welcome email to contact.
	 *
	 * You can find more information https://support.omnisend.com/en/articles/1061818-welcome-email-automation
	 *
	 * @param $birthday
	 *
	 * @return void
	 */
	public function set_welcome_email( $send_welcome_email ): void {
		$this->send_welcome_email = $send_welcome_email;
	}

	/**
	 * Sets email opt in source. It's used to track where contact opted in to receive emails. It's required to mark contact email as subscribed.
	 *
	 * Common format is `form:form_name` or `popup:popup_name`.
	 *
	 * @param $opt_in_text
	 *
	 * @return void
	 */
	public function set_email_opt_in( $opt_in_text ): void {
		$this->email_opt_in_source = $opt_in_text;
	}


	/**
	 * Sets phone opt in source. It's used to track where contact opted in to receive emails. It's required to mark contact phone as subscribed.
	 *
	 * Common format is `form:form_name` or `popup:popup_name`.
	 *
	 * @param $opt_in_text
	 *
	 * @return void
	 */
	public function set_phone_opt_in( $opt_in_text ): void {
		$this->phone_opt_in_source = $opt_in_text;
	}

	/**
	 * Sets email concent status. It's needed for GDPR compliance.
	 *
	 * Common format is `form:form_name` or `popup:popup_name`.
	 *
	 * @param $consent_text
	 *
	 * @return void
	 */
	public function set_email_consent( $consent_text ): void {
		$this->email_consent = $consent_text;
	}

	/**
	 * Sets email concent status. It's needed for GDPR compliance.
	 *
	 * Common format is `form:form_name` or `popup:popup_name`.
	 *
	 * @param $consent_text
	 *
	 * @return void
	 */
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
			$key = Utils::clean_up_custom_property_name( $key );
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

<?php

/**
 * Omnisend Contact Client
 *
 * @package OmnisendClient
 */

if (!defined('ABSPATH')) {
	exit;
}


class Omnisend_Core_Contact
{

	private $first_name      = null;
	private $last_name       = null;
	private $email           = null;
	private $address         = null;
	private $city            = null;
	private $state           = null;
	private $country         = null;
	private $postal_code     = null;
	private $phone           = null;
	private $birthday        = null;
	private $gender          = null;
	private $send_welcome_email = false;

	private array $tags            = array('wordpress');
	private ?string $email_consent = null;
	private ?string $phone_consent = null;

	private ?string $email_opt_in_source = null;
	private ?string $phone_opt_in_source = null;

	private array $custom_properties = array();

	private array $errors = array();


	// TODO add logic to validate main properties
	public function is_valid(): bool
	{
		$string_properties = array(
			'first_name', 'last_name', 'email', 'address',
			'city', 'state', 'country', 'postal_code', 'phone', 'birthday', 'gender',
			'email_consent', 'phone_consent', 'email_opt_in_source', 'phone_opt_in_source'
		);

		foreach ($string_properties as $property) {
			if ($this->$property != null && !is_string($this->$property)) {
				$this->errors[$property] = 'Not a string.';
			}
		}

		if ($this->email != null && !is_email($this->email) && $this->errors['email'] == null) {
			$this->errors['email'] = 'Not a valid email.';
		}

		// todo update validation for phone number
		if ($this->phone != null && !is_numeric($this->phone) && $this->errors['phone'] == null) {
			$this->errors['phone'] = 'Not a valid phone number.';
		}

		if ($this->phone == null && $this->email == null){
			$this->errors['identifier'] = 'Phone or email must be set.';
		}

		if ($this->gender != null && ($this->gender != 'f' ||  $this->gender != 'm')) {
			$this->errors['gender'] = 'Gender must be "f" or "m".';
		}


		foreach ($this->tags as $tag) {
			if (!Omnisend_Core_Client_Utils::is_valid_tag($tag)) {
				$this->errors['tags'] = 'Tag "'. $tag . '" is not valid. Please cleanup it before setting it. ';
				break;
			}
		}

		foreach ($this->custom_properties as $custom_property) {
			if (!Omnisend_Core_Client_Utils::is_valid_custom_property_name($custom_property)) {
				$this->errors['custom_properties'] = 'Custom property "'. $custom_property . '" is not valid. Please cleanup it before setting it. ';
				break;
			}
		}



		return count($this->errors) === 0;
	}

	public function errors()
	{
		return $this->errors;
	}

	public function to_array(): array
	{
		if (!$this->is_valid()) {
			return array();
		}

		$time_now = gmdate('c');

		$user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
		$ip         = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));

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
		if ($this->email_consent) {
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

		if ($this->custom_properties) {
			$arr['customProperties'] = $this->custom_properties;
		}

		if ($this->phone) {
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
			if ($this->phone_consent) {
				$phone_identifier['consent'] = array(
					'source'    => $this->phone_consent,
					'createdAt' => $time_now,
					'ip'        => $ip,
					'userAgent' => $user_agent,
				);
			}
			$arr['identifiers'][] = $phone_identifier;
		}

		if ($this->first_name) {
			$arr['firstName'] = $this->first_name;
		}

		if ($this->last_name) {
			$arr['lastName'] = $this->last_name;
		}

		if ($this->address) {
			$arr['address'] = $this->address;
		}

		if ($this->city) {
			$arr['city'] = $this->city;
		}

		if ($this->state) {
			$arr['state'] = $this->state;
		}

		if ($this->country) {
			$arr['country'] = $this->country;
		}

		if ($this->postal_code) {
			$arr['postalCode'] = $this->postal_code;
		}

		if ($this->birthday) {
			$arr['birthdate'] = $this->birthday;
		}

		if ($this->gender) {
			$arr['gender'] = $this->gender;
		}

		if ($this->send_welcome_email) {
			$arr['sendWelcomeEmail'] = $this->send_welcome_email;
		}

		return $arr;
	}


	// TODO change setters to simple so either way we would set them.
	public function set_email($email): void
	{
		if ($email && is_string($email)) {
			$this->email = $email;
		}
	}

	public function set_gender($gender): void
	{
		$this->gender = $gender;
	}

	public function set_first_name($first_name): void
	{
		$this->first_name = $first_name;
	}

	public function set_last_name($last_name): void
	{
		$this->last_name = $last_name;
	}

	public function set_address($address): void
	{
		$this->address = $address;
	}

	public function set_city($city): void
	{
		$this->city = $city;
	}

	public function set_state($state): void
	{
		$this->state = $state;
	}

	public function set_country($country): void
	{
		$this->country = $country;
	}

	public function set_postal_code($postal_code): void
	{
		$this->postal_code = $postal_code;
	}

	public function set_phone($phone): void
	{
		$this->phone = $phone;
	}

	public function set_birthday($birthday): void
	{
		$this->birthday = $birthday;
	}

	public function set_welcome_email($send_welcome_email): void
	{
		$this->send_welcome_email = $send_welcome_email;
	}

	// todo split into two functions to also allow full concent management and provide more properties
	public function set_email_opt_in($opt_in_text): void
	{
		$this->email_opt_in_source = $opt_in_text;
	}

	public function set_phone_opt_in($opt_in_text): void
	{
		$this->phone_opt_in_source = $opt_in_text;
	}

	public function set_email_consent($consent_text): void
	{
		$this->email_consent = $consent_text;
	}

	public function set_phone_consent($consent_text): void
	{
		$this->phone_consent = $consent_text;
	}


	public function add_custom_property($key, $value): void
	{
		$this->custom_properties[$key] = $value;
	}

	public function add_tag($tag): void
	{
		$this->tags[] = $tag;
	}
}



// // Usage:

// $person = new Contact();
// $person->name = 'John';
// $person->birth_date = '1930-01-01';
// $person->birth_date = '1930-01-01';

// this will be auto set for opt in && consent
// $person->creation_source = 'form';
// $person->email = 'john@example';
// $person->phone = '123456789';
// $person->add_tag('tag1');
// $person->add_tag('tag2');
// $person->add_custom_property('custom1', 'value1');
// $person->add_custom_property('custom2', 'value2');


// $person->set_email_consent('contact form');
// $person->set_phone_consent('contact form');

// if (!$person->is_valid())
// {
// Handle errors with $person->errors()
// }

// omnisend.save_contact($person) // also call to is_valid() inside

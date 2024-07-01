<?php

/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use WP_Error;

defined('ABSPATH') || die('no direct access');

/**
 * Omnisend Event class. It's should be used with Omnisend Client.
 *
 */
class Event
{
	private $contact        = null; //from contacts
	private $event_name     = null;
	private $event_time     = null;
	private $origin         = null;
	private $properties     = null;

	/**
	 * Validate event properties.
	 *
	 * todo kas yra reuquired? ka reikia vailiduoti?
	 * It ensures that phone or email is set and that they are valid. In addition other properties are validated if they are expected type and format.
	 *
	 * @return WP_Error
	 */
	public function validate(): WP_Error
	{

		$error = new WP_Error();

		if ($this->contact != null) {
			$contactError = $this->contact->validate;
			if (is_wp_error($contactError)) {
				$error->add($contactError);
			}
		}

		return $error;
	}

	/**
	 * Sets event name.
	 *
	 * @param $event_name
	 *
	 * @return void
	 */
	public function set_event_name($event_name): void
	{
		$this->event_name = $event_name;
	}

	/**
	 * Sets event time.
	 *
	 * @param $event_time
	 *
	 * @return void
	 */
	public function set_event_time($event_time): void
	{
		$this->event_time = $event_time;
	}


	/**
	 * Sets event origin.". //todo event origin ar just origin?
	 *
	 * @param $origin
	 *
	 * @return void
	 */
	public function set_origin($origin): void
	{
		$this->origin = $origin;
	}

	/**
	 * Sets event properties. //todo event properties ar just properties?
	 *
	 * @param $properties
	 *
	 * @return void
	 */
	public function set_properties($properties): void
	{
		$this->properties = $properties;
	}

	/**
	 * Sets contact.
	 *
	 * @param $contact
	 *
	 * @return void
	 */
	public function set_contact($contact): void
	{
		$this->contact = $contact;
	}
}

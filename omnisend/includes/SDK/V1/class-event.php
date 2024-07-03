<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use Omnisend\SDK\V1\Contact;
use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

/**
 * Omnisend Event class. It's should be used with Omnisend Client.
 *
 */
class Event {


	private $contact          = null;
	private $event_name       = null;
	private $event_time       = null;
	private $origin           = null;
	private array $properties = array();

	/**
	 * Validate event properties.
	 *
	 * It ensures that all required properties are set
	 *
	 * @return WP_Error
	 */
	public function validate(): WP_Error {

		$error = new WP_Error();
		if ( $contact instanceof Contact ) {
			$error->merge_from( $contact->validate() );
		}

		if ( $this->$event_name == null ) {
			$error->add( 'event_name', 'Is required.' );
		}

		if ( $this->$event_name != null && ! is_string( $this->$event_name ) ) {
			$error->add( $event_name, 'Not a string.' );
		}

		if ( $this->$origin != null && ! is_string( $this->$origin ) ) {
			$error->add( $origin, 'Not a string.' );
		}

		foreach ( $properties as $name => $value ) {
			if ( ! is_string( $name ) ) {
				$error->add( $name, 'Not a string.' );
			}
			if ( ! is_string( $value ) ) {
				$error->add( $value, 'Not a string.' );
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
	public function set_event_name( $event_name ): void {
		$this->event_name = $event_name;
	}

	/**
	 * Sets event time.
	 *
	 * @param $event_time
	 *
	 * @return void
	 */
	public function set_event_time( $event_time ): void {
		$this->event_time = $event_time;
	}


	/**
	 * Sets event origin.
	 *
	 * @param $origin
	 *
	 * @return void
	 */
	public function set_origin( $origin ): void {
		$this->origin = $origin;
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return void
	 */
	public function add_properties( $key, $value ): void {
		if ( $key == '' ) {
			return;
		}

		$this->properties[ $key ] = $value;
	}

	/**
	 * Sets contact.
	 *
	 * @param $contact
	 *
	 * @return void
	 */
	public function set_contact( $contact ): void {
		$this->contact = $contact;
	}

	/**
	 * Convert event to array.
	 *
	 * If event is valid it will be transformed to array that can be sent to Omnisend.
	 *
	 * @return array
	 */
	public function to_array(): array {
		if ( $this->validate()->has_errors() ) {
			return array();
		}

		$time_now = gmdate( 'c' );

		$arr = array();

		if ( $this->contact ) {
			$arr['contact'] = $this->contact->to_array_for_event();
		}

		if ( $this->event_name ) {
			$arr['eventName'] = $this->event_name;
		}

		if ( $this->event_time ) {
			$arr['eventTime'] = $this->event_time;
		} else {
			$arr['eventTime'] = $this->$time_now;
		}

		if ( $this->origin ) {
			$arr['origin'] = $this->origin;
		}

		if ( $this->properties ) {
			$arr['properties'] = $this->properties;
		}

		return $arr;
	}
}

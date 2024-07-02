<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1;

use WP_Error;

defined( 'ABSPATH' ) || die( 'no direct access' );

/**
 * Omnisend Event contact class. It's should be used with Omnisend Client.
 *
 */
class EventContact {


	private $id    = null;
	private $email = null;
	private $phone = null;

	/**
	 * Validate event properties.
	 *
	 * todo kas yra reuquired? ka reikia vailiduoti?
	 * It ensures that phone or email is set and that they are valid. In addition other properties are validated if they are expected type and format.
	 *
	 * @return WP_Error
	 */
	public function validate(): WP_Error {

		$error = new WP_Error();

		if ( $this->email != null && ! is_email( $this->email ) ) {
			$error->add( 'email', 'Not a email.' );
		}

		if ( $this->phone == null && $this->email == null && $this->id == null ) {
			$error->add( 'identifier', 'Phone or email or ID must be set.' );
		}

		$string_properties = array(
			'id',
			'email',
			'phone',
		);

		foreach ( $string_properties as $property ) {
			if ( $this->$property != null && ! is_string( $this->$property ) ) {
				$error->add( $property, 'Not a string.' );
			}
		}

		return $error;
	}

	/**
	 * Sets event name.
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function set_id( $id ): void {
		$this->id = $id;
	}

	/**
	 * Sets event time.
	 *
	 * @param $email
	 *
	 * @return void
	 */
	public function set_email( $email ): void {
		$this->email = $email;
	}


	/**
	 * Sets event phone.
	 *
	 * @param $phone
	 *
	 * @return void
	 */
	public function set_phone( $phone ): void {
		$this->phone = $phone;
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

		$arr = array();

		if ( $this->id ) {
			$arr['id'] = $this->id;
		}

		if ( $this->email ) {
			$arr['email'] = $this->email;
		}

		if ( $this->phone ) {
			$arr['phone'] = $this->phone;
		}

		return $arr;
	}
}

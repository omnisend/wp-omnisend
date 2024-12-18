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
 * Omnisend Batch class. It should be used with Omnisend Client.
 *
 */
class Batch {
	public const PRODUCTS_ENDPOINT   = 'products';
	public const CATEGORIES_ENDPOINT = 'categories';
	public const EVENTS_ENDPOINT     = 'events';
	public const CONTACTS_ENDPOINT   = 'contacts';
	public const POST_METHOD         = 'POST';
	public const PUT_METHOD          = 'PUT';

	private const REQUIRED_PROPERTIES = array(
		'endpoint',
		'items',
		'method',
	);
	private const STRING_PROPERTIES   = array(
		'endpoint',
		'method',
		'origin',
	);
	private const ARRAY_PROPERTIES    = array(
		'items',
	);
	private const AVAILABLE_ENDPOINTS = array(
		self::PRODUCTS_ENDPOINT,
		self::CATEGORIES_ENDPOINT,
		self::EVENTS_ENDPOINT,
		self::CONTACTS_ENDPOINT,
	);
	private const AVAILABLE_METHODS   = array(
		self::POST_METHOD,
		self::PUT_METHOD,
	);

	/**
	 * @var string $endpoint
	 */
	private $endpoint = null;

	/**
	 * @var array $items
	 */
	private $items = null;

	/**
	 * @var string $method
	 */
	private $method = null;

	/**
	 * @var string $origin
	 */
	private $origin = null;

	/**
	 * Validate batch properties.
	 *
	 * It ensures that required properties are set and that they are valid.
	 *
	 * @return WP_Error
	 */
	public function validate(): WP_Error {
		$error = new WP_Error();
		$error = $this->validate_properties( $error );

		if ( $error->has_errors() ) {
			return $error;
		}

		$error = $this->validate_values( $error );

		return $error;
	}

	/**
	 * Convert batch to array
	 *
	 * If batch is valid it will be transformed to array that can be sent to Omnisend.
	 *
	 * @return array
	 */
	public function to_array(): array {
		if ( $this->validate()->has_errors() ) {
			return array();
		}

		$arr = array(
			'endpoint' => $this->endpoint,
			'items'    => $this->items,
			'method'   => $this->method,
		);

		if ( ! empty( $this->origin ) ) {
			$arr['origin'] = $this->origin;
		}

		return $arr;
	}

	/**
	 * Sets endpoint
	 *
	 * @param string $endpoint
	 *
	 * @return void
	 */
	public function set_endpoint( $endpoint ): void {
		$this->endpoint = $endpoint;
	}

	/**
	 * Sets batch items
	 *
	 * @param array $items
	 *
	 * @return void
	 */
	public function set_items( $items ): void {
		$this->items = $items;
	}

	/**
	 * Sets method, it can be "PUT" or "POST"
	 *
	 * @param string $method
	 *
	 * @return void
	 */
	public function set_method( $method ): void {
		$this->method = $method;
	}

	/**
	 * Sets origin of request
	 *
	 * @param string $origin
	 *
	 * @return void
	 */
	public function set_origin( $origin ): void {
		$this->origin = $origin;
	}

	/**
	 * Validates property type
	 *
	 * @param WP_Error $error
	 *
	 * @return WP_Error $error
	 */
	private function validate_properties( WP_Error $error ): WP_Error {
		foreach ( $this as $property_key => $property_value ) {
			if ( in_array( $property_key, self::REQUIRED_PROPERTIES ) && empty( $property_value ) ) {
				$error->add( $property, $property_key . ' is a required property.' );
			}

			if ( $property_value !== null && in_array( $property_key, self::STRING_PROPERTIES ) && ! is_string( $property_value ) ) {
				$error->add( $property, $property_key . ' must be a string.' );
			}

			if ( $property_value !== null && in_array( $property_key, self::ARRAY_PROPERTIES ) && ! is_array( $property_value ) ) {
				$error->add( $property, $property_key . ' must be an array.' );
			}
		}

		return $error;
	}

	/**
	 * Validates property value
	 *
	 * @param WP_Error $error
	 *
	 * @return WP_Error $error
	 */
	private function validate_values( WP_Error $error ): WP_Error {
		if ( ! in_array( $this->endpoint, self::AVAILABLE_ENDPOINTS ) ) {
			$error->add( 'endpoint', sprintf( 'Endpoint must be one of the following: %s', implode( ', ', self::AVAILABLE_ENDPOINTS ) ) );
		}

		if ( ! in_array( $this->method, self::AVAILABLE_METHODS ) ) {
			$error->add( 'method', sprintf( 'Method must be on of the following: %s', implode( ', ', self::AVAILABLE_METHODS ) ) );
		}

		if ( empty( $this->items ) || count( $this->items ) > 1000 ) {
			$error->add( 'items', sprintf( 'Items are empty or batch size limit: %s was exceeded', 1000 ) );
		}

		return $error;
	}
}

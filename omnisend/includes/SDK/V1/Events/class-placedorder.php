<?php
/**
 * Omnisend Client
 *
 * @package OmnisendClient
 */

namespace Omnisend\SDK\V1\Events;

use WP_Error;
use Omnisend\Internal\Utils;
use Omnisend\SDK\V1\Events\Components\Address;
use Omnisend\SDK\V1\Events\Components\LineItem;
use Omnisend\SDK\V1\Events\Components\Tracking;
use Omnisend\SDK\V1\Events\Components\Discount;

defined( 'ABSPATH' ) || die( 'no direct access' );

/**
 * Omnisend PlacedOrder class. It should be used with Omnisend Event.
 */
class PlacedOrder {
	public const EVENT_NAME = 'placed order';

	private const REQUIRED_PROPERTIES          = array(
		'currency',
		'id',
		'number',
		'fulfillment_status',
		'payment_status',
		'payment_method',
		'subtotal_price',
		'subtotal_tax_included',
		'total_discount',
		'total_price',
		'total_tax',
		'line_items',
		'address',
		'created_at',
	);
	private const STRING_PROPERTIES            = array(
		'created_at',
		'currency',
		'fulfillment_status',
		'status_url',
		'id',
		'payment_status',
		'payment_method',
		'shipping_method',
		'note',
	);
	private const NUMERIC_PROPERTIES           = array(
		'shipping_price',
		'subtotal_price',
		'total_discount',
		'total_price',
		'total_tax',
		'number',
	);
	private const AVAILABLE_FULFILLMENT_STATUS = array(
		'unfulfilled',
		'inProgress',
		'fulfilled',
		'delivered',
		'restocked',
	);
	private const AVAILABLE_PAYMENT_STATUS     = array(
		'awaitingPayment',
		'partiallyPaid',
		'paid',
		'partiallyRefunded',
		'refunded',
		'voided',
	);

	/**
	 * @var string $created_at
	 */
	private $created_at = null;

	/**
	 * @var string $currency
	 */
	private $currency = null;

	/**
	 * @var string $fulfillment_status
	 */
	private $fulfillment_status = null;

	/**
	 * @var string $status_url
	 */
	private $status_url = null;

	/**
	 * @var string $note
	 */
	private $note = null;

	/**
	 * @var string $id
	 */
	private $id = null;

	/**
	 * @var int $number
	 */
	private $number = null;

	/**
	 * @var string $payment_status
	 */
	private $payment_status = null;

	/**
	 * @var string $payment_method
	 */
	private $payment_method = null;

	/**
	 * @var string $shipping_method
	 */
	private $shipping_method = null;

	/**
	 * @var mixed $shipping_price
	 */
	private $shipping_price = null;

	/**
	 * @var mixed $subtotal_price
	 */
	private $subtotal_price = null;

	/**
	 * @var bool $subtotal_tax_included
	 */
	private $subtotal_tax_included = null;

	/**
	 * @var mixed $total_discount
	 */
	private $total_discount = null;

	/**
	 * @var mixed $total_price
	 */
	private $total_price = null;

	/**
	 * @var mixed $total_tax
	 */
	private $total_tax = null;

	/**
	 * @var Address $address
	 */
	private $address = null;

	/**
	 * @var Tracking $tracking
	 */
	private $tracking = null;

	/**
	 * @var array $discounts
	 */
	private array $discounts = array();

	/**
	 * @var array $line_items
	 */
	private array $line_items = array();

	/**
	 * @var array $tags
	 */
	private array $tags = array();

	/**
	 * Sets order address component
	 *
	 * @param Address $address
	 *
	 * @return void
	 */
	public function set_address( $address ): void {
		$this->address = $address;
	}

	/**
	 * Sets order created_at, format: "Y-m-d\Th:i:s\Z"
	 *
	 * @param string $created_at
	 *
	 * @return void
	 */
	public function set_created_at( $created_at ): void {
		$this->created_at = $created_at;
	}

	/**
	 * Sets order currency
	 *
	 * @param string $currency
	 *
	 * @return void
	 */
	public function set_currency( $currency ): void {
		$this->currency = $currency;
	}

	/**
	 * Sets order fulfillment status
	 *
	 * @param string $fulfillment_status
	 *
	 * @return void
	 */
	public function set_fulfillment_status( $fulfillment_status ): void {
		$this->fulfillment_status = $fulfillment_status;
	}

	/**
	 * Sets order note
	 *
	 * @param string $note
	 *
	 * @return void
	 */
	public function set_note( $note ): void {
		$this->note = $note;
	}

	/**
	 * Sets order ID
	 *
	 * @param string $id
	 *
	 * @return void
	 */
	public function set_id( $id ): void {
		$this->id = $id;
	}

	/**
	 * Sets order number
	 *
	 * @param int $number
	 *
	 * @return void
	 */
	public function set_number( $number ): void {
		$this->number = $number;
	}

	/**
	 * Sets order status URL
	 *
	 * @param string $status_url
	 *
	 * @return void
	 */
	public function set_status_url( $status_url ): void {
		$this->status_url = $status_url;
	}

	/**
	 * Sets order payment method
	 *
	 * @param string $payment_method
	 *
	 * @return void
	 */
	public function set_payment_method( $payment_method ): void {
		$this->payment_method = $payment_method;
	}

	/**
	 * Sets order payment status
	 *
	 * @param string $payment_status
	 *
	 * @return void
	 */
	public function set_payment_status( $payment_status ): void {
		$this->payment_status = $payment_status;
	}

	/**
	 * Sets order shipping method
	 *
	 * @param string $shipping_method
	 *
	 * @return void
	 */
	public function set_shipping_method( $shipping_method ): void {
		$this->shipping_method = $shipping_method;
	}

	/**
	 * Sets order shipping price
	 *
	 * @param mixed $shipping_price
	 *
	 * @return void
	 */
	public function set_shipping_price( $shipping_price ): void {
		$this->shipping_price = $shipping_price;
	}

	/**
	 * Sets order subtotal price (amount)
	 *
	 * @param mixed $subtotal_price
	 *
	 * @return void
	 */
	public function set_subtotal_price( $subtotal_price ): void {
		$this->subtotal_price = $subtotal_price;
	}

	/**
	 * Sets flag, if order subtotal is with tax
	 *
	 * @param bool $subtotal_tax_included
	 *
	 * @return void
	 */
	public function set_subtotal_tax_included( $subtotal_tax_included ): void {
		$this->subtotal_tax_included = $subtotal_tax_included;
	}

	/**
	 * Sets order total discount
	 *
	 * @param mixed $total_discount
	 *
	 * @return void
	 */
	public function set_total_discount( $total_discount ): void {
		$this->total_discount = $total_discount;
	}

	/**
	 * Sets order total price
	 *
	 * @param mixed $total_price
	 *
	 * @return void
	 */
	public function set_total_price( $total_price ): void {
		$this->total_price = $total_price;
	}

	/**
	 * Sets order total tax
	 *
	 * @param mixed $total_tax
	 *
	 * @return void
	 */
	public function set_total_tax( $total_tax ): void {
		$this->total_tax = $total_tax;
	}

	/**
	 * Sets order Tracking componenet
	 *
	 * @param Tracking $tracking
	 *
	 * @return void
	 */
	public function set_tracking( $tracking ): void {
		$this->tracking = $tracking;
	}

	/**
	 * Adds order tag
	 *
	 * @param string $tag
	 * @param bool   $clean_up_tag
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

	/**
	 * Adds order discount component
	 *
	 * @param Discount $discount
	 *
	 * @return void
	 */
	public function add_discount( $discount ): void {
		$this->discounts[] = $discount;
	}

	/**
	 * Adds order LineItem component
	 *
	 * @param LineItem $line_item
	 *
	 * @return void
	 */
	public function add_line_item( $line_item ): void {
		$this->line_items[] = $line_item;
	}

	/**
	 * Converts PlacedOrder to array.
	 *
	 * If PlacedOrder is valid it will be transformed to array that can be used with Event
	 *
	 * @return array
	 */
	public function to_array(): array {
		if ( $this->validate()->has_errors() ) {
			return array();
		}

		$arr = array();

		if ( ! empty( $this->tags ) ) {
			$arr['tags'] = array_values( array_unique( $this->tags ) );
		}

		if ( $this->created_at !== null ) {
			$arr['createdAt'] = $this->created_at;
		}

		if ( $this->currency !== null ) {
			$arr['currency'] = $this->currency;
		}

		if ( $this->fulfillment_status !== null ) {
			$arr['fulfillmentStatus'] = $this->fulfillment_status;
		}

		if ( $this->id !== null ) {
			$arr['orderID'] = $this->id;
		}

		if ( $this->number !== null ) {
			$arr['orderNumber'] = $this->number;
		}

		if ( $this->payment_method !== null ) {
			$arr['paymentMethod'] = $this->payment_method;
		}

		if ( $this->payment_status !== null ) {
			$arr['paymentStatus'] = $this->payment_status;
		}

		if ( $this->subtotal_price !== null ) {
			$arr['subTotalPrice'] = $this->subtotal_price;
		}

		if ( $this->subtotal_tax_included !== null ) {
			$arr['subtotalTaxIncluded'] = $this->subtotal_tax_included;
		}

		if ( $this->total_tax !== null ) {
			$arr['totalTax'] = $this->total_tax;
		}

		if ( $this->total_discount !== null ) {
			$arr['totalDiscount'] = $this->total_discount;
		}

		if ( $this->total_price !== null ) {
			$arr['totalPrice'] = $this->total_price;
		}

		if ( $this->shipping_price !== null ) {
			$arr['shippingPrice'] = $this->shipping_price;
		}

		if ( $this->status_url !== null ) {
			$arr['orderStatusURL'] = $this->status_url;
		}

		if ( $this->note !== null ) {
			$arr['note'] = $this->note;
		}

		if ( $this->shipping_method !== null ) {
			$arr['shippingMethod'] = $this->shipping_method;
		}

		if ( $this->tracking !== null ) {
			$arr['tracking'] = $this->tracking->to_array();
		}

		foreach ( $this->line_items as $item ) {
			$arr['lineItems'][] = $item->to_array();
		}

		foreach ( $this->discounts as $discount ) {
			$arr['discounts'][] = $discount->to_array();
		}

		if ( $this->address !== null ) {
			$arr['shippingAddress'] = $this->address->to_array_shipping();
			$arr['billingAddress']  = $this->address->to_array_billing();
		}

		return $arr;
	}

	/**
	 * Validates properties.
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
	 * Validates property types
	 *
	 * @param WP_Error $error
	 *
	 * @return WP_Error
	 */
	private function validate_properties( WP_Error $error ): WP_Error {
		foreach ( $this as $property_key => $property_value ) {
			if ( in_array( $property_key, self::REQUIRED_PROPERTIES ) && $property_value === null ) {
				$error->add( $property_key, $property_key . ' is a required property' );
			}

			if ( $property_value !== null && in_array( $property_key, self::STRING_PROPERTIES ) && ! is_string( $property_value ) ) {
				$error->add( $property_key, $property_key . ' must be a string' );
			}

			if ( $property_value !== null && in_array( $property_key, self::NUMERIC_PROPERTIES ) && ! is_numeric( $property_value ) ) {
				$error->add( $property_key, $property_key . ' must be a string' );
			}
		}

		if ( ! is_bool( $this->subtotal_tax_included ) ) {
			$error->add( 'subtotal_tax_included', 'Subtotal tax included should be a boolean' );
		}

		if ( $this->tracking !== null && ! $this->tracking instanceof Tracking ) {
			$error->add( 'Tracking', 'Tracking is not ant instance of Tracking' );
		}

		if ( $this->address !== null && ! $this->address instanceof Address ) {
			$error->add( 'Address', 'Address is not an instance of Address' );
		}

		foreach ( $this->discounts as $discount ) {
			if ( ! $discount instanceof Discount ) {
				$error->add( 'discounts', 'Discount is not an instance of Discounts' );
			}
		}

		foreach ( $this->line_items as $item ) {
			if ( ! $item instanceof LineItem ) {
				$error->add( 'line_item', 'Line Item is not an instance of LineItem' );
			}
		}

		return $error;
	}

	/**
	 * Validates property values
	 *
	 * @param WP_Error $error
	 *
	 * @return WP_Error
	 */
	private function validate_values( WP_Error $error ): WP_Error {
		foreach ( $this->discounts as $discount ) {
			$error->merge_from( $discount->validate() );
		}

		foreach ( $this->line_items as $item ) {
			$error->merge_from( $item->validate() );
		}

		if ( $this->tracking !== null ) {
			$error->merge_from( $this->tracking->validate() );
		}

		if ( $this->address !== null ) {
			$error->merge_from( $this->address->validate() );
		}

		if ( ! in_array( $this->fulfillment_status, self::AVAILABLE_FULFILLMENT_STATUS ) ) {
			$error->add( 'fulfillment_status', 'Fulfillment status is not one of ' . implode( ',', self::AVAILABLE_FULFILLMENT_STATUS ) );
		}

		if ( ! in_array( $this->payment_status, self::AVAILABLE_PAYMENT_STATUS ) ) {
			$error->add( 'payment_status', 'Payment status is not one of ' . implode( ',', self::AVAILABLE_PAYMENT_STATUS ) );
		}

		if ( ! ctype_upper( $this->currency ) ) {
			$error->add( 'currency', 'Currency code must be all uppercase' );
		}

		return $error;
	}
}

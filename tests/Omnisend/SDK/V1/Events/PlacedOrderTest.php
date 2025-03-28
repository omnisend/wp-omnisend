<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\PlacedOrder;
use Omnisend\SDK\V1\Events\Components\Address;
use Omnisend\SDK\V1\Events\Components\Discount;
use Omnisend\SDK\V1\Events\Components\ProductCategory;
use Omnisend\SDK\V1\Events\Components\Tracking;
use Omnisend\SDK\V1\Events\Components\LineItem;
use PHPUnit\Framework\TestCase;

final class PlacedOrderTest extends TestCase
{
    public function test_event_fails_with_undefined_data(): void {
        $event = new PlacedOrder();

        $expected_result = array(
            'created_at' => array('created_at is a required property'),
            'currency' => array('currency is a required property'),
            'fulfillment_status' => array('fulfillment_status is a required property'),
            'id' => array('id is a required property'),
            'number' => array('number is a required property'),
            'payment_status' => array('payment_status is a required property'),
            'payment_method' => array('payment_method is a required property'),
            'subtotal_price' => array('subtotal_price is a required property'),
            'subtotal_tax_included' => array('subtotal_tax_included is a required property', 'Subtotal tax included should be a boolean'),
            'total_discount' => array('total_discount is a required property'),
            'total_price' => array('total_price is a required property'),
            'total_tax' => array('total_tax is a required property'),
            'address' => array('address is a required property'),
        );

        $this->assertEquals($event->validate()->errors, $expected_result);
    }

    public function test_event_fails_with_invalid_data(): void {
        $event = new PlacedOrder();

        $event->set_address('omnisend');
        $event->set_created_at('yesterday');
        $event->set_currency('eur');
        $event->set_fulfillment_status('in progress');
        $event->set_note(444);
        $event->set_id(array());
        $event->set_number('test');
        $event->set_status_url('status');
        $event->set_payment_method(4444);
        $event->set_payment_status('status');
        $event->set_shipping_method(444);
        $event->set_shipping_price('test');
        $event->set_subtotal_price('test');
        $event->set_subtotal_tax_included('test');
        $event->set_total_discount('test');
        $event->set_total_price('test');
        $event->set_total_tax('test');
        
        $event->set_tracking('test');
        $event->add_discount('test');
        $event->add_line_item('test');

        $expected_result = array(
            'note' => array('note must be a string'),
            'id' => array('id must be a string'),
            'number' => array('number must be a number'),
            'payment_method' => array('payment_method must be a string'),
            'shipping_method' => array('shipping_method must be a string'),
            'shipping_price' => array('shipping_price must be a number'),
            'subtotal_price' => array('subtotal_price must be a number'),
            'total_discount' => array('total_discount must be a number'),
            'total_price' => array('total_price must be a number'),
            'total_tax' => array('total_tax must be a number'),
            'subtotal_tax_included' => array('Subtotal tax included should be a boolean'),
            'Tracking' => array('Tracking is not an instance of Tracking'),
            'Address' => array('Address is not an instance of Address'),
            'discounts' => array('Discount is not an instance of Discount'),
            'line_item' => array('Line Item is not an instance of LineItem')
        );

        $this->assertEquals($event->validate()->errors, $expected_result);
    }

    public function test_event_raises_validation_error_on_incorrect_address(): void {
        $event = new PlacedOrder();

        $event->set_address('omnisend');

        $error_message = $event->validate()->get_error_message('Address');
        $expected_error_message = 'Address is not an instance of Address';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_note(): void {
        $event = new PlacedOrder();

        $event->set_note(444);

        $error_message = $event->validate()->get_error_message('note');
        $expected_error_message = 'note must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_id(): void {
        $event = new PlacedOrder();

        $event->set_id(array());

        $error_message = $event->validate()->get_error_message('id');
        $expected_error_message = 'id must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_tracking(): void {
        $event = new PlacedOrder();
        
        $event->set_tracking('test');

        $error_message = $event->validate()->get_error_message('Tracking');
        $expected_error_message = 'Tracking is not an instance of Tracking';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_discounts(): void {
        $event = new PlacedOrder();

        $event->add_discount('test');
        $event->add_discount(array('test2'));
        $event->add_discount(null);

        $error_message = $event->validate()->get_error_message('discounts');
        $expected_error_message = 'Discount is not an instance of Discount';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_line_items(): void {
        $event = new PlacedOrder();

        $event->add_line_item('test');
        $event->add_line_item(array('id' => 123, 'title' => 'my product'));

        $error_message = $event->validate()->get_error_message('line_item');
        $expected_error_message = 'Line Item is not an instance of LineItem';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_total_price(): void {
        $event = new PlacedOrder();

        $event->set_total_price('123test');

        $error_message = $event->validate()->get_error_message('total_price');
        $expected_error_message = 'total_price must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_total_discount(): void {
        $event = new PlacedOrder();

        $event->set_total_discount(array(10));

        $error_message = $event->validate()->get_error_message('total_discount');
        $expected_error_message = 'total_discount must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_tag(): void {
        $event = new PlacedOrder();
        $line_item = new LineItem();
        $address = new Address();
        $tracking = new Tracking();
        $discount = new Discount();

        $event->set_created_at(date('Y-m-d\Th:i:s\Z', '1641328224'));
        $event->set_currency('EUR');
        $event->set_fulfillment_status('delivered');
        $event->set_note('Can courier leave my order by the porch ?');
        $event->set_id('59598');
        $event->set_number('145');
        $event->set_status_url('https://omnisend.com/orders/view_status/order?id=59598&auth_key=959128sada%$@$^&dsa!');
        $event->set_payment_method('manual');
        $event->set_payment_status('paid');
        $event->set_shipping_method('my shipment method');
        $event->set_shipping_price(2.99);
        $event->set_subtotal_price(29.99);
        $event->set_subtotal_tax_included(false);
        $event->set_total_discount(10);
        $event->set_total_price(19.99);
        $event->set_total_tax(4.1979);
        
        $event->set_address($address);
        $event->set_tracking($tracking);
        $event->add_discount($discount);
        $event->add_line_item($line_item);

        $clean_up_tag = false;
        $event->add_tag('!@#$%^&*() ? : } : test', $clean_up_tag);

        $error_message = $event->validate()->get_error_message('tags');
        $expected_error_message = 'Tag "!@#$%^&*() ? : } : test" is not valid. Please cleanup it before setting it.';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_passes_validation(): void {
        $event = new PlacedOrder();
        $line_item = new LineItem();
        $address = new Address();
        $tracking = new Tracking();
        $discount = new Discount();

        $address->set_billing_address_1('my street');
        $address->set_shipping_address_1('my street1');
        $address->set_billing_city('My town');
        $address->set_shipping_city('My town');
        $address->set_billing_country('My country');
        $address->set_shipping_country('My country');
        $address->set_billing_first_name('Tom');
        $address->set_shipping_first_name('John');
        $address->set_billing_last_name('Smith');
        $address->set_shipping_last_name('Smith');
        $address->set_billing_phone('+37088888888');
        $address->set_shipping_phone('+37077777777');
        $address->set_billing_state('my state');
        $address->set_shipping_state('my other state');
        $address->set_shipping_state_code('CA');
        $address->set_billing_state_code('LA');
        $address->set_shipping_zip('12345');
        $address->set_billing_zip('54321');
        $address->set_billing_company('Omnisend');

        $tracking->set_code('9598684');
        $tracking->set_courier_title('my courier');
        $tracking->set_courier_url('https://omnisend.com/tracking/track_shipment/9598684');

        $discount->set_amount(10);
        $discount->set_code('my_coupon_code');
        $discount->set_type('15_percent_off_for_every_new_customer');

        $line_item->set_title('my product');
        $line_item->set_discount(10);
        $line_item->set_description('test');
        $line_item->set_id('my_product_id');
        $line_item->set_image_url('https://omnisend.com/images/image.png');
        $line_item->set_price(19.99);
        $line_item->set_strike_through_price(29.99);
        $line_item->set_quantity(1);
        $line_item->set_sku('SKU1234567890');
        $line_item->set_url('https://omnisend.com/products/product');
        $line_item->set_vendor('My vendor');
        $line_item->set_weight(0.6452);
        $line_item->set_variant_id('my_product_id_for_variant');
        $line_item->set_variant_image_url('https://omnisend.com/images/image-2.png');
        $line_item->set_variant_title('my product - extended warranty 3 years');

        $event->set_created_at(date('Y-m-d\Th:i:s\Z', '1641328224'));
        $event->set_currency('EUR');
        $event->set_fulfillment_status('delivered');
        $event->set_note('Can courier leave my order by the porch ?');
        $event->set_id('59598');
        $event->set_number('145');
        $event->set_status_url('https://omnisend.com/orders/view_status/order?id=59598&auth_key=959128sada%$@$^&dsa!');
        $event->set_payment_method('manual');
        $event->set_payment_status('paid');
        $event->set_shipping_method('my shipment method');
        $event->set_shipping_price(2.99);
        $event->set_subtotal_price(29.99);
        $event->set_subtotal_tax_included(false);
        $event->set_total_discount(10);
        $event->set_total_price(19.99);
        $event->set_total_tax(4.1979);
        $event->add_tag('source Omnisend');
        
        $event->set_address($address);
        $event->set_tracking($tracking);
        $event->add_discount($discount);
        $event->add_line_item($line_item);

        $this->assertFalse($event->validate()->has_errors());

        $event = $event->to_array();

        $expected_result = array(
            'createdAt' => '2022-01-04T08:30:24Z',
            'currency' => 'EUR',
            'fulfillmentStatus' => 'delivered',
            'orderID' => '59598',
            'orderNumber' => '145',
            'paymentMethod' => 'manual',
            'paymentStatus' => 'paid',
            'subTotalPrice' => 29.99,
            'subtotalTaxIncluded' => false,
            'totalTax' => 4.1979,
            'totalDiscount' => 10,
            'totalPrice' => 19.99,
            'shippingPrice' => 2.99,
            'orderStatusURL' => 'https://omnisend.com/orders/view_status/order?id=59598&auth_key=959128sada%$@$^&dsa!',
            'note' => 'Can courier leave my order by the porch ?',
            'shippingMethod' => 'my shipment method',
            'tracking' => array(
                'code' => '9598684',
                'courierTitle' => 'my courier',
                'courierURL' => 'https://omnisend.com/tracking/track_shipment/9598684'
            ),
            'discounts' => array(
                array(
                    'amount' => 10,
                    'code' => 'my_coupon_code',
                    'type' => '15_percent_off_for_every_new_customer'
                )
            ),
            'shippingAddress' => array(
                'address1' => 'my street1',
                'city' => 'My town',
                'country' => 'My country',
                'firstName' => 'John',
                'lastName' => 'Smith',
                'phone' => '+37077777777',
                'state' => 'my other state',
                'stateCode' => 'CA',
                'zip' => '12345'
            ),
            'billingAddress' => array(
                'address1' => 'my street',
                'city' => 'My town',
                'company' => 'Omnisend',
                'country' => 'My country',
                'firstName' => 'Tom',
                'lastName' => 'Smith',
                'phone' => '+37088888888',
                'state' => 'my state',
                'stateCode' => 'LA',
                'zip' => '54321'
            ),
            'tags' => array(
                'source Omnisend'
            ),
            'lineItems' => array(
                array(
                    'productDescription' => 'test',
                    'productDiscount' => 10,
                    'productID' => 'my_product_id',
                    'productImageURL' => 'https://omnisend.com/images/image.png',
                    'productPrice' => 19.99,
                    'productStrikeThroughPrice' => 29.99,
                    'productQuantity' => 1,
                    'productSKU' => 'SKU1234567890',
                    'productTitle' => 'my product',
                    'productURL' => 'https://omnisend.com/products/product',
                    'productVariantID' => 'my_product_id_for_variant',
                    'productVariantImageURL' => 'https://omnisend.com/images/image-2.png',
                    'productVariantTitle' => 'my product - extended warranty 3 years',
                    'productVendor' => 'My vendor',
                    'productWeight' => 0.6452
                )
            )

        );

        $this->assertEquals($event, $expected_result);
    }
}

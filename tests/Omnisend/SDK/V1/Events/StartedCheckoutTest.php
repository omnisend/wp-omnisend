<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\StartedCheckout;
use Omnisend\SDK\V1\Events\Components\LineItem;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../../dependencies/dependencies.php' );

final class StartedCheckoutTest extends TestCase
{
    public function test_event_fails_with_undefined_data(): void {
        $event = new StartedCheckout();

        $expected_result = array(
            'abandoned_checkout_url' => array('abandoned_checkout_url is a required property'),
            'cart_id' => array('cart_id is a required property'),
            'currency' => array('currency is a required property'),
            'value' => array('value is a required property')
        );

        $this->assertEquals($event->validate()->errors, $expected_result);
    }

    public function test_event_fails_with_invalid_data(): void {
        $event = new StartedCheckout();

        $event->set_abandoned_checkout_url(array('invalid'));
        $event->set_cart_id((object)array('not array'));
        $event->set_currency(4);
        $event->set_value('test');
        $event->add_line_item('test1');

        $expected_result = array(
            'abandoned_checkout_url' => array('abandoned_checkout_url must be a string'),
            'cart_id' => array('cart_id must be a string'),
            'currency' => array('currency must be a string'),
            'value' => array('value must be a number'),
            'line_item' => array('Line Item is not an instance of LineItem')
        );

        $this->assertEquals($event->validate()->errors, $expected_result);
    }

    public function test_event_raises_validation_error_on_incorrect_abandoned_checkout_url(): void {
        $event = new StartedCheckout();

        $event->set_abandoned_checkout_url('test');
        $event->set_cart_id('123');
        $event->set_currency('EUR');
        $event->set_value(29.99);

        $error_message = $event->validate()->get_error_message('abandoned_checkout_url');
        $expected_error_message = 'Abandoned checkout URL must contain a valid URL';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_value(): void {
        $event = new StartedCheckout();

        $event->set_value(array('not a value'));

        $error_message = $event->validate()->get_error_message('value');
        $expected_error_message = 'value must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_error_on_incorrect_currency(): void {
        $event = new StartedCheckout();

        $event->set_currency(array('EUR'));

        $error_message = $event->validate()->get_error_message('currency');
        $expected_error_message = 'currency must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_raises_validation_errors_on_incorrect_line_item(): void {
        $event = new StartedCheckout();
        $line_item = new LineItem();

        $line_item->set_discount(array('test'));

        $event->set_abandoned_checkout_url('https://omnisend.com/checkout');
        $event->set_cart_id('123');
        $event->set_currency('EUR');
        $event->set_value(19.99);
        $event->add_line_item($line_item);

        $error_message = $event->validate()->get_error_message('variant_id');
        $expected_error_message = 'variant_id is a required property';

        $this->assertEquals($error_message, $expected_error_message);

        $error_message = $event->validate()->get_error_message('discount');
        $expected_error_message = 'discount must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_event_passes_validation(): void {
        $event = new StartedCheckout();
        $line_item = new LineItem();

        $line_item->set_title('my product');
        $line_item->set_discount(19);
        $line_item->set_description('test');
        $line_item->set_id('my_product_id');
        $line_item->set_image_url('https://omnisend.com/images/image.png');
        $line_item->set_price(10.99);
        $line_item->set_strike_through_price(29.99);
        $line_item->set_quantity(2);
        $line_item->set_sku('SKU1234567890');
        $line_item->set_url('https://omnisend.com/products/product');
        $line_item->set_vendor('My vendor');
        $line_item->set_weight(0.6452);

        $line_item->set_variant_id('my_product_id_for_variant');
        $line_item->set_variant_image_url('https://omnisend.com/images/image-2.png');
        $line_item->set_variant_title('my product - extended warranty 3 years');

        $event->set_abandoned_checkout_url('https://omnisend.com/checkout');
        $event->set_cart_id('123');
        $event->set_currency('EUR');
        $event->set_value(21.98);
        $event->add_line_item($line_item);

        $this->assertFalse($event->validate()->has_errors());

        $event = $event->to_array();

        $expected_result = array(
            'abandonedCheckoutURL' => 'https://omnisend.com/checkout',
            'cartID' => '123',
            'currency' => 'EUR',
            'value' => 21.98,
            'lineItems' => array(
                array(
                    'productDescription' => 'test',
                    'productDiscount' => 19,
                    'productID' => 'my_product_id',
                    'productImageURL' => 'https://omnisend.com/images/image.png',
                    'productPrice' => 10.99,
                    'productStrikeThroughPrice' => 29.99,
                    'productQuantity' => 2,
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

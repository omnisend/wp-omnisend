<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\Components\LineItem;
use PHPUnit\Framework\TestCase;

final class LineItemTest extends TestCase
{
    public function test_line_item_fails_with_undefined_data(): void {
        $line_item = new LineItem();

        $expected_result = array(
            'id' => array('id is a required property'),
            'price' => array('price is a required property'),
            'quantity' => array('quantity is a required property'),
            'title' => array('title is a required property'),
            'variant_id' => array('variant_id is a required property'),
        );

        $this->assertEquals($line_item->validate()->errors, $expected_result);
    }

    public function test_line_item_fails_with_invalid_data(): void {
        $line_item = new LineItem();

        $line_item->set_title(123);
        $line_item->set_discount('test');
        $line_item->set_description(123);
        $line_item->set_id((object) 1);
        $line_item->set_image_url(123);
        $line_item->set_price('test');
        $line_item->set_strike_through_price('test');
        $line_item->set_quantity('test');
        $line_item->set_sku(array());
        $line_item->set_url('test');
        $line_item->set_vendor(000555);
        $line_item->set_weight('test');
        $line_item->set_variant_id(444);
        $line_item->set_variant_image_url('test');
        $line_item->set_variant_title(454);

        $expected_result = array(
            'description' => array('description must be a string'),
            'discount' => array('discount must be a number'),
            'id' => array('id must be a string'),
            'image_url' => array('image_url must be a string'),
            'price' => array('price must be a number'),
            'quantity' => array('quantity must be a number'),
            'sku' => array('sku must be a string'),
            'title' => array('title must be a string'),
            'variant_title' => array('variant_title must be a string'),
            'variant_id' => array('variant_id must be a string'),
            'vendor' => array('vendor must be a string'),
            'weight' => array('weight must be a number'),
            'strike_through_price' => array('strike_through_price must be a number'),
        );

        $this->assertEquals($line_item->validate()->errors, $expected_result);
    }

    public function test_line_item_raises_validation_error_on_incorrect_title(): void {
        $line_item = new LineItem();

        $line_item->set_title(123);

        $error_message = $line_item->validate()->get_error_message('title');
        $expected_error_message = 'title must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_discount(): void {
        $line_item = new LineItem();

        $line_item->set_discount('test');

        $error_message = $line_item->validate()->get_error_message('discount');
        $expected_error_message = 'discount must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_weight(): void {
        $line_item = new LineItem();

        $line_item->set_weight('test');

        $error_message = $line_item->validate()->get_error_message('weight');
        $expected_error_message = 'weight must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_url(): void {
        $line_item = new LineItem();

        $line_item->set_id('test');
        $line_item->set_price(1);
        $line_item->set_quantity(1);
        $line_item->set_title('my product');
        $line_item->set_variant_id('test_1');

        $line_item->set_url('not URL');

        $error_message = $line_item->validate()->get_error_message('url');
        $expected_error_message = 'Product must contain a valid URL';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_image_url(): void {
        $line_item = new LineItem();

        $line_item->set_id('test');
        $line_item->set_price(1);
        $line_item->set_quantity(1);
        $line_item->set_title('my product');
        $line_item->set_variant_id('test_1');

        $line_item->set_image_url(5555);

        $error_message = $line_item->validate()->get_error_message('image_url');
        $expected_error_message = 'image_url must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_strike_through_price(): void {
        $line_item = new LineItem();

        $line_item->set_strike_through_price('not a price');

        $error_message = $line_item->validate()->get_error_message('strike_through_price');
        $expected_error_message = 'strike_through_price must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_price(): void {
        $line_item = new LineItem();

        $line_item->set_price(array('not a price'));

        $error_message = $line_item->validate()->get_error_message('price');
        $expected_error_message = 'price must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_vendor(): void {
        $line_item = new LineItem();

        $line_item->set_vendor(new LineItem());

        $error_message = $line_item->validate()->get_error_message('vendor');
        $expected_error_message = 'vendor must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_variant_id(): void {
        $line_item = new LineItem();

        $line_item->set_variant_id(111.232);

        $error_message = $line_item->validate()->get_error_message('variant_id');
        $expected_error_message = 'variant_id must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_raises_validation_error_on_incorrect_quantity(): void {
        $line_item = new LineItem();

        $line_item->set_quantity('test');

        $error_message = $line_item->validate()->get_error_message('quantity');
        $expected_error_message = 'quantity must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_line_item_passes_validation(): void {
        $line_item = new LineItem();

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

        $this->assertFalse($line_item->validate()->has_errors());

        $line_item = $line_item->to_array();

        $expected_result = array(
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
        );

        $this->assertEquals($line_item, $expected_result);
    }
}

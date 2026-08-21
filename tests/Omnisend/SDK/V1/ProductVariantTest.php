<?php

namespace Omnisend\SDK\V1;

use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../dependencies/dependencies.php' );

final class ProductVariantTest extends TestCase
{
    public function test_fails_with_undefined_data(): void {
        $variant = new ProductVariant();

        $expected_result = array(
            'id' => array('id is a required property'),
            'price' => array('price is a required property'),
            'status' => array('status is a required property'),
            'title' => array('title is a required property'),
            'url' => array('url is a required property')
        );

        $this->assertEquals($expected_result, $variant->validate()->errors);
    }

    public function test_passes_validation(): void {
        $this->assertFalse($this->variant()->validate()->has_errors());
    }

    public function test_id_of_101_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_id(str_repeat('v', 101));

        $this->assertEquals('ID must be under 100 characters', $variant->validate()->get_error_message('id'));
    }

    public function test_id_with_unsupported_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_id('variant 1');

        $this->assertEquals(
            'ID must contain only letters, numbers, underscores and dashes',
            $variant->validate()->get_error_message('id')
        );
    }

    public function test_title_of_255_characters_passes_validation(): void {
        $variant = $this->variant();
        $variant->set_title(str_repeat('a', 255));

        $this->assertFalse($variant->validate()->has_errors());
    }

    public function test_title_of_256_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_title(str_repeat('a', 256));

        $this->assertEquals('Title must be under 255 characters', $variant->validate()->get_error_message('title'));
    }

    public function test_sku_of_255_characters_passes_validation(): void {
        $variant = $this->variant();
        $variant->set_sku(str_repeat('s', 255));

        $this->assertFalse($variant->validate()->has_errors());
    }

    public function test_sku_of_256_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_sku(str_repeat('s', 256));

        $this->assertEquals('SKU must be under 255 characters', $variant->validate()->get_error_message('sku'));
    }

    public function test_description_of_1000_characters_passes_validation(): void {
        $variant = $this->variant();
        $variant->set_description(str_repeat('a', 1000));

        $this->assertFalse($variant->validate()->has_errors());
    }

    public function test_description_of_1001_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_description(str_repeat('a', 1001));

        $this->assertEquals(
            'Description must be under 1000 characters',
            $variant->validate()->get_error_message('description')
        );
    }

    public function test_negative_price_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_price(-1);

        $this->assertEquals(
            'Price must not be negative and must have no more than 2 decimal places',
            $variant->validate()->get_error_message('price')
        );
    }

    public function test_price_with_three_decimal_places_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_price(9.999);

        $this->assertEquals(
            'Price must not be negative and must have no more than 2 decimal places',
            $variant->validate()->get_error_message('price')
        );
    }

    public function test_strike_through_price_with_three_decimal_places_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_strike_through_price(19.999);

        $this->assertEquals(
            'Strike through price must not be negative and must have no more than 2 decimal places',
            $variant->validate()->get_error_message('strike_through_price')
        );
    }

    public function test_url_of_1001_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_url('https://omnisend.com/products/' . str_repeat('a', 1000));

        $this->assertEquals('Url must be under 1000 characters', $variant->validate()->get_error_message('url'));
    }

    public function test_default_image_url_of_1001_characters_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_default_image_url('https://omnisend.com/media/' . str_repeat('a', 1000) . '.png');

        $this->assertEquals(
            'Default image URL must be under 1000 characters',
            $variant->validate()->get_error_message('default_image_url')
        );
    }

    public function test_300_images_pass_validation(): void {
        $variant = $this->variant();

        for ($i = 0; $i < 300; $i++) {
            $variant->add_image('https://omnisend.com/media/products/product-' . $i . '.png');
        }

        $this->assertFalse($variant->validate()->has_errors());
    }

    public function test_301_images_fail_validation(): void {
        $variant = $this->variant();

        for ($i = 0; $i < 301; $i++) {
            $variant->add_image('https://omnisend.com/media/products/product-' . $i . '.png');
        }

        $this->assertEquals('Images must not exceed 300 items', $variant->validate()->get_error_message('images'));
    }

    public function test_image_that_is_not_url_fails_validation(): void {
        $variant = $this->variant();
        $variant->add_image('media/product.png');

        $this->assertEquals(
            'Image "media/product.png" must contain a valid URL',
            $variant->validate()->get_error_message('images')
        );
    }

    public function test_unsupported_status_fails_validation(): void {
        $variant = $this->variant();
        $variant->set_status('in stock');

        $this->assertEquals(
            'Status must be one of the following: inStock,outOfStock,notAvailable',
            $variant->validate()->get_error_message('status')
        );
    }

    private function variant(): ProductVariant {
        $variant = new ProductVariant();
        $variant->set_id('product-1-variant-1');
        $variant->set_price(9.99);
        $variant->set_status(Product::STATUS_IN_STOCK);
        $variant->set_title('My variant');
        $variant->set_url('https://omnisend.com/products/my-product');

        return $variant;
    }
}

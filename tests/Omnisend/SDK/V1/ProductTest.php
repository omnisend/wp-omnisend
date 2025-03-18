<?php

namespace Omnisend\SDK\V1;

use Omnisend\Internal\ProductFactory;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function test_factory_fails_with_undefined_data(): void {
        $product_data = array();
        $product = ProductFactory::create_product($product_data);

        $expected_result = array(
            'currency' => array('currency is a required property'),
            'id' => array('id is a required property'),
            'status' => array('status is a required property'),
            'title' => array('title is a required property'),
            'url' => array('url is a required property')
        );

        $this->assertEquals($product->validate()->errors, $expected_result);
    }

    public function test_factory_fails_with_all_invalid_data(): void {
        $product_data = array(
            'categoryIDs' => 'category1, category2',
            'variants' => 'variant1, variant2',
            'images' => 'image1, image2',
            'createdAt' => array('Yesterday'),
            'currency' => '$',
            'defaultImageUrl' => false,
            'description' => true,
            'id' => 0003,
            'status' => 1,
            'tags' => false,
            'title' => array('Product'),
            'type' => 50,
            'updatedAt' => array('Today'),
            'url' => null,
            'vendor' => 123
        );

        $product = ProductFactory::create_product($product_data);

        $expected_result = array(
            'created_at' => array('created_at must be a string'),
            'default_image_url' => array('default_image_url must be a string'),
            'description' => array('description must be a string'),
            'id' => array('id must be a string'),
            'status' => array('status must be a string'),
            'title' => array('title must be a string'),
            'type' => array('type must be a string'),
            'updated_at' => array('updated_at must be a string'),
            'url' => array('url is a required property'),
            'vendor' => array('vendor must be a string'),
        );

        $this->assertEquals($product->validate()->errors, $expected_result);
    }

    public function test_factory_raises_validation_error_on_long_title(): void {
        $product_data = array(
            'title' => '
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
            ',
            'currency' => 'EUR',
            'id' => '123',
            'status' => 'inStock',
            'url' => 'https://omnisend.com/product'
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('title');
        $expected_error_message = 'Title must be under 100 characters';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_lowercase_currency(): void {
        $product_data = array(
            'title' => 'My product',
            'currency' => 'eur',
            'id' => '123',
            'status' => 'inStock',
            'url' => 'https://omnisend.com/product'
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('currency');
        $expected_error_message = 'Currency code must be all uppercase';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_incorrect_status(): void {
        $product_data = array(
            'title' => 'My product',
            'currency' => 'EUR',
            'id' => '123',
            'status' => 'in stock',
            'url' => 'https://omnisend.com/product'
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('status');
        $expected_error_message = 'Status must be one of the following: inStock,outOfStock,notAvailable';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_incorrect_url(): void {
        $product_data = array(
            'title' => 'My product',
            'currency' => 'EUR',
            'id' => '123',
            'status' => 'inStock',
            'url' => 'my-store/products/product-2'
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('url');
        $expected_error_message = 'Url must contain a valid URL';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_incorrect_variant_price(): void {
        $product_data = array(
            'title' => 'My product',
            'currency' => 'USD',
            'id' => '123',
            'status' => 'inStock',
            'url' => 'https://omnisend.com/my-products/my-product',
            'variants' => array(
                array(
                    'id' => '123-1',
                    'title' => 'test',
                    'url' => 'https://omnisend.com/my-products/my-product',
                    'price' => 'incorrect price'
                )
            )
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('price');
        $expected_error_message = 'price must be a number';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_missing_variant_id(): void {
        $product_data = array(
            'title' => 'My product',
            'currency' => 'USD',
            'id' => '123',
            'status' => 'inStock',
            'url' => 'https://omnisend.com/my-products/my-product',
            'variants' => array(
                array(
                    'title' => 'test',
                    'url' => 'https://omnisend.com/my-products/my-product',
                    'price' => 14.52,
                )
            )
        );

        $product = ProductFactory::create_product($product_data);

        $error_message = $product->validate()->get_error_message('id');
        $expected_error_message = 'id is a required property';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_passes_validation(): void {
        $product_data = array(
            'categoryIDs' => array( 
                'category_id_1',
                'category_id_2'
            ),
            'variants' => array(
                array(
                    'defaultImageUrl' => 'https://omnisend.com/media/products/product-8.png',
                    'description' => 'My description',
                    'id' => '3006',
                    'images' => array(
                        'https://omnisend.com/media/products/product-1.png',
                        'https://omnisend.com/media/products/product-2.png'
                    ),
                    'price' => 9.99,
                    'sku' => 'SKU123456789',
                    'status' => 'inStock',
                    'strikeThroughPrice' => 19.99,
                    'title' => 'My product - 1 year extended warranty',
                    'url' => 'https://omnisend.com/products/my-product'
                ),
                array(
                    'defaultImageUrl' => 'https://omnisend.com/media/products/product-3.png',
                    'description' => 'My description',
                    'id' => '3005',
                    'images' => array(
                        'https://omnisend.com/media/products/product-4.png',
                        'https://omnisend.com/media/products/product-5.png'
                    ),
                    'price' => 15.99,
                    'sku' => 'SKU1234567891',
                    'status' => 'notAvailable',
                    'strikeThroughPrice' => 25.99,
                    'title' => 'My product - 3 year extended warranty',
                    'url' => 'https://omnisend.com/products/my-product'
                )
            ),
            'images' => array(
                'https://omnisend.com/media/products/product-6.png',
                'https://omnisend.com/media/products/product-7.png'
            ),
            'createdAt' => date('Y-m-d\Th:i:s\Z', '1641328224'),
            'currency' => 'USD',
            'defaultImageUrl' => 'https://omnisend.com/media/products/product.png',
            'description' => 'My description',
            'id' => '00015',
            'status' => 'inStock',
            'tags' => array(
                'Electronics',
                'Tag2',
                'Tag3'
            ),
            'title' => 'My product',
            'type' => 'Super Product',
            'updatedAt' => date('Y-m-d\Th:i:s\Z', strtotime('2023-01-01 13:34:27')),
            'url' => 'https://omnisend.com/products/my-product',
            'vendor' => 'My vendor'
        );

        $product = ProductFactory::create_product($product_data);

        $this->assertFalse($product->validate()->has_errors());

        $product = $product->to_array();

        $expected_result = array(
            'tags' => array(
                'Electronics',
                'Tag2',
                'Tag3'
            ),
            'currency' => 'USD',
            'id' => '00015',
            'status' => 'inStock',
            'title' => 'My product',
            'url' => 'https://omnisend.com/products/my-product',
            'categoryIDs' => array(
                'category_id_1',
                'category_id_2'
            ),
            'createdAt' => '2022-01-04T08:30:24Z',
            'defaultImageUrl' => 'https://omnisend.com/media/products/product.png',
            'description' => 'My description',
            'images' => array(
                'https://omnisend.com/media/products/product-6.png',
                'https://omnisend.com/media/products/product-7.png'
            ),
            'type' => 'Super Product',
            'updatedAt' => '2023-01-01T01:34:27Z',
            'vendor' => 'My vendor',
            'variants' => array(
                array(
                    'id' => '3006',
                    'price' => 9.99,
                    'title' => 'My product - 1 year extended warranty',
                    'url' => 'https://omnisend.com/products/my-product',
                    'defaultImageUrl' => 'https://omnisend.com/media/products/product-8.png',
                    'description' => 'My description',
                    'images' => array(
                        'https://omnisend.com/media/products/product-1.png',
                        'https://omnisend.com/media/products/product-2.png'
                    ),
                    'sku' => 'SKU123456789',
                    'status' => 'inStock',
                    'strikeThroughPrice' => 19.99
                ),
                array(
                    'id' => '3005',
                    'price' => 15.99,
                    'title' => 'My product - 3 year extended warranty',
                    'url' => 'https://omnisend.com/products/my-product',
                    'defaultImageUrl' => 'https://omnisend.com/media/products/product-3.png',
                    'description' => 'My description',
                    'images' => array(
                        'https://omnisend.com/media/products/product-4.png',
                        'https://omnisend.com/media/products/product-5.png'
                    ),
                    'sku' => 'SKU1234567891',
                    'status' => 'notAvailable',
                    'strikeThroughPrice' => 25.99,
                )
            ),
        );

        $this->assertEquals($product, $expected_result);
    }
}

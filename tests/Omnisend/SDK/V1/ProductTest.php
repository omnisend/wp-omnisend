<?php

namespace Omnisend\SDK\V1;

use Omnisend\Internal\ProductFactory;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../dependencies/dependencies.php' );

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
        $expected_error_message = 'Title must be under 255 characters';

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
            'createdAt' => date('Y-m-d\Th:i:s\Z', strtotime('2022-01-04 08:30:24')),
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

    public function test_title_of_255_characters_passes_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('title' => str_repeat('a', 255))));

        $this->assertFalse($product->validate()->has_errors());
    }

    public function test_title_of_256_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('title' => str_repeat('a', 256))));

        $this->assertEquals('Title must be under 255 characters', $product->validate()->get_error_message('title'));
    }

    public function test_description_of_1000_characters_passes_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('description' => str_repeat('a', 1000))));

        $this->assertFalse($product->validate()->has_errors());
    }

    public function test_description_of_1001_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('description' => str_repeat('a', 1001))));

        $this->assertEquals('Description must be under 1000 characters', $product->validate()->get_error_message('description'));
    }

    public function test_id_of_101_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('id' => str_repeat('p', 101))));

        $this->assertEquals('ID must be under 100 characters', $product->validate()->get_error_message('id'));
    }

    public function test_id_with_unsupported_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('id' => 'product 1')));

        $this->assertEquals(
            'ID must contain only letters, numbers, underscores and dashes',
            $product->validate()->get_error_message('id')
        );
    }

    public function test_url_of_1001_characters_fails_validation(): void {
        $url = 'https://omnisend.com/products/' . str_repeat('a', 1000);
        $product = ProductFactory::create_product($this->product_data(array('url' => $url)));

        $this->assertEquals('Url must be under 1000 characters', $product->validate()->get_error_message('url'));
    }

    public function test_default_image_url_of_1001_characters_fails_validation(): void {
        $image_url = 'https://omnisend.com/media/' . str_repeat('a', 1000) . '.png';
        $product = ProductFactory::create_product($this->product_data(array('defaultImageUrl' => $image_url)));

        $this->assertEquals(
            'Default image URL must be under 1000 characters',
            $product->validate()->get_error_message('default_image_url')
        );
    }

    public function test_300_images_pass_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('images' => $this->image_urls(300))));

        $this->assertFalse($product->validate()->has_errors());
    }

    public function test_301_images_fail_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('images' => $this->image_urls(301))));

        $this->assertEquals('Images must not exceed 300 items', $product->validate()->get_error_message('images'));
    }

    public function test_image_that_is_not_url_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('images' => array('media/product.png'))));

        $this->assertEquals(
            'Image "media/product.png" must contain a valid URL',
            $product->validate()->get_error_message('images')
        );
    }

    public function test_100_category_ids_pass_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('categoryIDs' => $this->category_ids(100))));

        $this->assertFalse($product->validate()->has_errors());
    }

    public function test_101_category_ids_fail_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('categoryIDs' => $this->category_ids(101))));

        $this->assertEquals(
            'Category IDs must not exceed 100 items',
            $product->validate()->get_error_message('category_ids')
        );
    }

    public function test_category_id_of_201_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('categoryIDs' => array(str_repeat('c', 201)))));

        $this->assertEquals(
            'Category ID must be under 200 characters',
            $product->validate()->get_error_message('category_ids')
        );
    }

    public function test_category_id_with_unsupported_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('categoryIDs' => array('category 1'))));

        $this->assertEquals(
            'Category ID "category 1" must contain only letters, numbers, underscores and dashes',
            $product->validate()->get_error_message('category_ids')
        );
    }

    public function test_101_tags_fail_validation(): void {
        $tags = array();

        for ($i = 0; $i < 101; $i++) {
            $tags[] = 'tag-' . $i;
        }

        $product = ProductFactory::create_product($this->product_data(array('tags' => $tags)));

        $this->assertEquals('Tags must not exceed 100 items', $product->validate()->get_error_message('tags'));
    }

    public function test_product_without_variants_fails_validation(): void {
        $product_data = $this->product_data();
        unset($product_data['variants']);

        $product = ProductFactory::create_product($product_data);

        $this->assertEquals('Product must have at least 1 variant', $product->validate()->get_error_message('variants'));
    }

    public function test_500_variants_pass_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('variants' => $this->variants(500))));

        $this->assertFalse($product->validate()->has_errors());
    }

    public function test_501_variants_fail_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('variants' => $this->variants(501))));

        $this->assertEquals('Variants must not exceed 500 items', $product->validate()->get_error_message('variants'));
    }

    public function test_type_of_101_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('type' => str_repeat('t', 101))));

        $this->assertEquals('Type must be under 100 characters', $product->validate()->get_error_message('type'));
    }

    public function test_vendor_of_101_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('vendor' => str_repeat('v', 101))));

        $this->assertEquals('Vendor must be under 100 characters', $product->validate()->get_error_message('vendor'));
    }

    public function test_currency_that_is_not_three_characters_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('currency' => 'US')));

        $this->assertEquals('Currency code must be 3 characters long', $product->validate()->get_error_message('currency'));
    }

    public function test_created_at_in_unsupported_format_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('createdAt' => '2022-01-04 08:30:24')));

        $this->assertEquals(
            'created_at must be in Y-m-d\TH:i:s\Z format',
            $product->validate()->get_error_message('created_at')
        );
    }

    public function test_updated_at_in_unsupported_format_fails_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('updatedAt' => '2022-01-04T08:30:24+02:00')));

        $this->assertEquals(
            'updated_at must be in Y-m-d\TH:i:s\Z format',
            $product->validate()->get_error_message('updated_at')
        );
    }

    public function test_updated_at_in_api_format_passes_validation(): void {
        $product = ProductFactory::create_product($this->product_data(array('updatedAt' => '2022-01-04T08:30:24Z')));

        $this->assertFalse($product->validate()->has_errors());
    }

    private function product_data(array $overrides = array()): array {
        $product_data = array(
            'currency' => 'USD',
            'id' => 'product-1',
            'status' => 'inStock',
            'title' => 'My product',
            'url' => 'https://omnisend.com/products/my-product',
            'variants' => $this->variants(1),
        );

        return array_merge($product_data, $overrides);
    }

    private function variants(int $count): array {
        $variants = array();

        for ($i = 0; $i < $count; $i++) {
            $variants[] = array(
                'id' => 'product-1-variant-' . $i,
                'price' => 9.99,
                'status' => 'inStock',
                'title' => 'My variant',
                'url' => 'https://omnisend.com/products/my-product',
            );
        }

        return $variants;
    }

    private function image_urls(int $count): array {
        $images = array();

        for ($i = 0; $i < $count; $i++) {
            $images[] = 'https://omnisend.com/media/products/product-' . $i . '.png';
        }

        return $images;
    }

    private function category_ids(int $count): array {
        $category_ids = array();

        for ($i = 0; $i < $count; $i++) {
            $category_ids[] = 'category-' . $i;
        }

        return $category_ids;
    }
}

<?php

namespace Omnisend\SDK\V1;

use Omnisend\Internal\CategoryFactory;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function test_factory_fails_with_undefined_data(): void {
        $category_data = [];
        $category = CategoryFactory::create_category($category_data);

        $expected_result = array(
            'category_id' => array('category_id is a required property.'),
            'title' => array('title is a required property.')
        );

        $this->assertEquals($category->validate()->errors, $expected_result);
    }

    public function test_factory_fails_with_invalid_data(): void {
        $category_data = array('categoryID' => 321, 'title' => 123);
        $category = CategoryFactory::create_category($category_data);

        $expected_result = array(
            'category_id' => array('category_id must be a string'),
            'title' => array('title must be a string')
        );

        $this->assertEquals($category->validate()->errors, $expected_result);
    }

    public function test_factory_raises_validation_error_on_long_title(): void {
        $category_data = array(
            'categoryID' => 'C1234',
            'title' => '
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
            '
        );

        $category = CategoryFactory::create_category($category_data);

        $error_message = $category->validate()->get_error_message('title');
        $expected_error_message = 'Title must be under 100 characters';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_raises_validation_error_on_long_category_id(): void {
        $category_data = array(
            'categoryID' => '
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
                0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
            ',
            'title' => 'Beauty products',
        );

        $category = CategoryFactory::create_category($category_data);

        $error_message = $category->validate()->get_error_message('category_id');
        $expected_error_message = 'Category ID must be under 100 characters';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_factory_passes_validation(): void {
        $category_data = array('categoryID' => 'C1234', 'title' => 'Beauty products');
        $category = CategoryFactory::create_category($category_data);

        $this->assertFalse($category->validate()->has_errors());

        $category = $category->to_array();
        
        $expected_result = array(
            'categoryId' => 'C1234',
            'title' => 'Beauty products'
        );

        $this->assertEquals($category, $expected_result);
    }
}

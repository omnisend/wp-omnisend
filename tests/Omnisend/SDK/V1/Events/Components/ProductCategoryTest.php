<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\Components\ProductCategory;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../../../dependencies/dependencies.php' );

final class ProductCategoryTest extends TestCase
{
    public function test_category_fails_with_undefined_data(): void {
        $category = new ProductCategory();

        $expected_result = array(
            'required_properties' => array('Title or ID should not be empty')
        );

        $this->assertEquals($category->validate()->errors, $expected_result);
    }

    public function test_category_fails_with_invalid_data(): void {
        $category = new ProductCategory();

        $category->set_id(array());
        $category->set_title(123);

        $expected_result = array(
            'id' => array('id must be a string'),
            'title' => array('title must be a string')
        );

        $this->assertEquals($category->validate()->errors, $expected_result);
    }

    public function test_category_raises_validation_error_on_incorrect_id(): void {
        $category = new ProductCategory();

        $category->set_id(array());
        $category->set_title('my category');

        $error_message = $category->validate()->get_error_message('id');
        $expected_error_messsage = 'id must be a string';

        $this->assertEquals($error_message, $expected_error_messsage);
    }

    public function test_category_raises_validation_error_on_long_title(): void {
        $category = new ProductCategory();

        $category->set_id('my_id');
        $category->set_title('
            0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
            0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
        ');

        $error_message = $category->validate()->get_error_message('title');
        $expected_error_messsage = 'Title should not exceed 100 characters';

        $this->assertEquals($error_message, $expected_error_messsage);
    }

    public function test_category_raises_validation_error_on_incorrect_title(): void {
        $category = new ProductCategory();

        $category->set_id('my_id');
        $category->set_title(123);

        $error_message = $category->validate()->get_error_message('title');
        $expected_error_messsage = 'title must be a string';

        $this->assertEquals($error_message, $expected_error_messsage);
    }

    public function test_category_passes_validation(): void {
        $category = new ProductCategory();

        $category->set_id('my_id');
        $category->set_title('my title');

        $this->assertFalse($category->validate()->has_errors());

        $category = $category->to_array();
        $expected_result = array(
            'id' => 'my_id',
            'title' => 'my title'
        );

        $this->assertEquals($category, $expected_result);
    }
}

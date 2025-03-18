<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\Components\Discount;
use PHPUnit\Framework\TestCase;

final class DiscountTest extends TestCase
{
    public function test_discount_fails_with_undefined_data(): void {
        $discount = new Discount();

        $expected_result = array(
            'amount' => array('amount is a required property')
        );

        $this->assertEquals($discount->validate()->errors, $expected_result);
    }

    public function test_discount_fails_with_invalid_data(): void {
        $discount = new Discount();

        $discount->set_amount('test');
        $discount->set_code(array());
        $discount->set_type(123);

        $expected_result = array(
            'amount' => array('amount must be a number'),
            'type' => array('type must be a string')
        );

        $this->assertEquals($discount->validate()->errors, $expected_result);
    }

    public function test_discount_raises_validation_error_on_incorrect_code(): void {
        $discount = new Discount();

        $discount->set_code(123);

        $error_message = $discount->validate()->get_error_message('code');
        $expected_error_message = 'code must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_discount_raises_validation_error_on_incorrect_amount(): void {
        $discount = new Discount();

        $discount->set_amount('test');

        $error_message = $discount->validate()->get_error_message('amount');
        $expected_error_message = 'amount must be a number';
        
        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_discount_raises_validation_error_on_incorrect_type(): void {
        $discount = new Discount();

        $discount->set_type(123);

        $error_message = $discount->validate()->get_error_message('type');
        $expected_error_message = 'type must be a string';
        
        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_discount_passes_validation(): void {
        $discount = new Discount();

        $discount->set_amount(10.01);
        $discount->set_code('test1');
        $discount->set_type('test2');
        
        $this->assertFalse($discount->validate()->has_errors());

        $discount = $discount->to_array();
        $expected_result = array(
            'amount' => 10.01,
            'code' => 'test1',
            'type' => 'test2'
        );

        $this->assertEquals($discount, $expected_result);
    }
}

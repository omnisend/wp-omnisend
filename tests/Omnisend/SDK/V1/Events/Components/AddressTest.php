<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\Components\Address;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../../../dependencies/dependencies.php' );

final class AddressTest extends TestCase
{
    public function test_address_fails_with_invalid_data(): void {
        $address = new Address();

        $address->set_billing_address_1(123);
        $address->set_shipping_address_1(321);
        $address->set_billing_city(222);
        $address->set_shipping_city(array());
        $address->set_billing_country((object)array());
        $address->set_shipping_country(4.542);
        $address->set_billing_first_name('');
        $address->set_shipping_first_name(222);
        $address->set_billing_last_name(new Address());
        $address->set_shipping_last_name(444);
        $address->set_billing_phone('test');
        $address->set_shipping_phone('test');
        $address->set_billing_state(44);
        $address->set_shipping_state(12);
        $address->set_shipping_state_code(22);
        $address->set_billing_state_code(33);
        $address->set_shipping_zip(44);
        $address->set_billing_zip(55);
        $address->set_billing_company(44);
        $address->set_shipping_company(new Address());

        $expected_result = array(
            'shipping_address_1' => array('shipping_address_1 must be a string'),
            'shipping_city' => array('shipping_city must be a string'),
            'shipping_company' => array('shipping_company must be a string'),
            'shipping_country' => array('shipping_country must be a string'),
            'shipping_first_name' => array('shipping_first_name must be a string'),
            'shipping_last_name' => array('shipping_last_name must be a string'),
            'shipping_state' => array('shipping_state must be a string'),
            'shipping_state_code' => array('shipping_state_code must be a string'),
            'shipping_zip' => array('shipping_zip must be a string'),
            'billing_address_1' => array('billing_address_1 must be a string'),
            'billing_city' => array('billing_city must be a string'),
            'billing_company' => array('billing_company must be a string'),
            'billing_country' => array('billing_country must be a string'),
            'billing_last_name' => array('billing_last_name must be a string'),
            'billing_state' => array('billing_state must be a string'),
            'billing_state_code' => array('billing_state_code must be a string'),
            'billing_zip' => array('billing_zip must be a string')
        );

        $this->assertEquals($address->validate()->errors, $expected_result);
    }

    public function test_address_raises_validation_error_on_incorrect_billing_address_1(): void {
        $address = new Address();

        $address->set_billing_address_1(123);

        $error_message = $address->validate()->get_error_message('billing_address_1');
        $expected_error_message = 'billing_address_1 must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_address_raises_validation_error_on_incorrect_shipping_address_2(): void {
        $address = new Address();

        $address->set_shipping_address_2(123);

        $error_message = $address->validate()->get_error_message('shipping_address_2');
        $expected_error_message = 'shipping_address_2 must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_address_passes_validation(): void {
        $address = new Address();

        $address->set_billing_address_1('my street');
        $address->set_shipping_address_1('my street2');
        $address->set_billing_city('My town');
        $address->set_shipping_city('My town');
        $address->set_billing_country('My country');
        $address->set_shipping_country('My country');
        $address->set_billing_first_name('John');
        $address->set_shipping_first_name('Jonathan');
        $address->set_billing_last_name('Smith');
        $address->set_shipping_last_name('SM');
        $address->set_billing_phone('+37088888888');
        $address->set_shipping_phone('+37077777777');
        $address->set_billing_state('my state');
        $address->set_shipping_state('my other state');
        $address->set_shipping_state_code('CA');
        $address->set_billing_state_code('LA');
        $address->set_shipping_zip('12345');
        $address->set_billing_zip('54321');
        $address->set_billing_company('Omnisend');
        $address->set_shipping_company('Omnisend 2');

        $this->assertFalse($address->validate()->has_errors());

        $address_shipping = $address->to_array_shipping();
        $address_billing = $address->to_array_billing();

        $expected_billing_result = array(
            'address1' => 'my street',
            'city' => 'My town',
            'company' => 'Omnisend',
            'country' => 'My country',
            'firstName' => 'John',
            'lastName' => 'Smith',
            'phone' => '+37088888888',
            'state' => 'my state',
            'stateCode' => 'LA',
            'zip' => '54321'
        );
        $expected_shipping_result = array(
            'address1' => 'my street2',
            'city' => 'My town',
            'company' => 'Omnisend 2',
            'country' => 'My country',
            'firstName' => 'Jonathan',
            'lastName' => 'SM',
            'phone' => '+37077777777',
            'state' => 'my other state',
            'stateCode' => 'CA',
            'zip' => '12345'
        );

        $this->assertEquals($address_shipping, $expected_shipping_result);
        $this->assertEquals($address_billing, $expected_billing_result);
    }
}

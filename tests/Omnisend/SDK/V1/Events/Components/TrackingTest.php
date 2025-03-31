<?php

namespace Omnisend\SDK\V1\Events;

use Omnisend\SDK\V1\Events\Components\Tracking;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../../../../dependencies/dependencies.php' );

final class TrackingTest extends TestCase
{
    public function test_tracking_fails_with_undefined_data(): void {
        $tracking = new Tracking();

        $expected_result = array(
            'required_properties' => array('Tracking code or courier title or courier URL should not be empty')
        );

        $this->assertEquals($tracking->validate()->errors, $expected_result);
    }

    public function test_tracking_fails_with_invalid_data(): void {
        $tracking = new Tracking();

        $tracking->set_code(123);
        $tracking->set_courier_title(123);
        $tracking->set_courier_url('not url');

        $expected_result = array(
            'code' => array('code must be a string'),
            'courier_title' => array('courier_title must be a string')
        );
        
        $this->assertEquals($tracking->validate()->errors, $expected_result);
    }

    public function test_tracking_raises_validation_error_on_incorrect_code(): void {
        $tracking = new Tracking();

        $tracking->set_code(54149);
        $tracking->set_courier_title('my courier');
        $tracking->set_courier_url('https://omnisend.com/tracking/track_shipment/id/54149');

        $error_message = $tracking->validate()->get_error_message('code');
        $expected_error_message = 'code must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_tracking_raises_validation_error_on_incorrect_courier_title(): void {
        $tracking = new Tracking();

        $tracking->set_code('54149');
        $tracking->set_courier_title(123);
        $tracking->set_courier_url('https://omnisend.com/tracking/track_shipment/id/54149');

        $error_message = $tracking->validate()->get_error_message('courier_title');
        $expected_error_message = 'courier_title must be a string';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_tracking_raises_validation_error_on_incorrect_courier_url(): void {
        $tracking = new Tracking();

        $tracking->set_code('54149');
        $tracking->set_courier_title('my courier');
        $tracking->set_courier_url('not url');

        $error_message = $tracking->validate()->get_error_message('courier_url');
        $expected_error_message = 'courier url must contain a valid URL';

        $this->assertEquals($error_message, $expected_error_message);
    }

    public function test_tracking_passes_validation(): void {
        $tracking = new Tracking();

        $tracking->set_code('54149');
        $tracking->set_courier_title('my courier');
        $tracking->set_courier_url('https://omnisend.com/tracking/track_shipment/id/54149');

        $this->assertFalse($tracking->validate()->has_errors());

        $tracking = $tracking->to_array();
        $expected_result = array(
            'code' => '54149',
            'courierTitle' => 'my courier',
            'courierURL' => 'https://omnisend.com/tracking/track_shipment/id/54149'
        );

        $this->assertEquals($tracking, $expected_result);
    }
}

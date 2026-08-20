<?php
namespace Omnisend\Internal\V1;

use Omnisend\Internal\ApiResponse;
use Omnisend\Internal\ContactFactory;
use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\Event;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../../dependencies/dependencies.php' );

final class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
    }

    private function client(): Client
    {
        return new Client('brandid-secret', 'test-plugin', '1.0.0', null);
    }

    private function contact(): Contact
    {
        $contact = new Contact();
        $contact->set_email('test@example.com');
        $contact->set_phone('+37060000000');
        $contact->set_welcome_email(true);
        $contact->add_tag('test-tag');

        return $contact;
    }

    private function event(): Event
    {
        $event = new Event();
        $event->set_custom_event_name('test-event');
        $event->set_contact($this->contact());

        return $event;
    }

    private function assert_common_headers(array $request): void
    {
        $this->assertEquals('Omnisend-API-Key brandid-secret', $request['args']['headers']['Authorization']);
        $this->assertEquals('2026-03-15', $request['args']['headers']['Omnisend-Version']);
    }

    public static function error_response_provider(): array
    {
        return array(
            array(400, ApiResponse::ERROR_HTTP),
            array(401, ApiResponse::ERROR_UNAUTHORIZED),
            array(403, ApiResponse::ERROR_FORBIDDEN),
            array(404, ApiResponse::ERROR_NOT_FOUND),
            array(409, ApiResponse::ERROR_CONFLICT),
            array(410, ApiResponse::ERROR_VERSION_RETIRED),
            array(429, ApiResponse::ERROR_RATE_LIMITED),
            array(500, ApiResponse::ERROR_SERVER),
        );
    }

    public function test_create_contact_sends_versioned_headers_and_returns_id(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":"contact-1"}'));

        $contact = ContactFactory::create_contact(array('email' => 'test@example.com'));
        $response = $this->client()->create_contact($contact);

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('contact-1', $response->get_contact_id());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/contacts', $request['url']);
        $this->assert_common_headers($request);
    }

    public function test_create_contact_reports_missing_id_as_unexpected_shape(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"contactID":"contact-1"}'));

        $contact = ContactFactory::create_contact(array('email' => 'test@example.com'));
        $response = $this->client()->create_contact($contact);

        $error = $response->get_wp_error();
        $this->assertEquals(ApiResponse::ERROR_UNEXPECTED_SHAPE, $error->get_error_code());
        $this->assertEquals('Contact id not found in response.', $error->get_error_message());
    }

    public function test_create_contact_accepts_existing_contact_upsert_response(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"id":"contact-1"}'));

        $response = $this->client()->create_contact($this->contact());

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('contact-1', $response->get_contact_id());
    }

    public function test_create_contact_sends_api_payload(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":"contact-1"}'));

        $this->client()->create_contact($this->contact());

        $request = WP_Http_Test_Stub::last_request();
        $payload = json_decode($request['args']['body'], true);
        $this->assertArrayNotHasKey('contactID', $payload);
        $this->assertArrayNotHasKey('sendWelcomeEmail', $payload);
        $this->assertArrayHasKey('sendWelcomeMessage', $payload['identifiers'][0]);
        $this->assertTrue($payload['identifiers'][0]['sendWelcomeMessage']);
        $this->assertTrue($payload['identifiers'][1]['sendWelcomeMessage']);
        $this->assertArrayHasKey('statusChangedAt', $payload['identifiers'][0]['channels']['email']);
        $this->assertArrayNotHasKey('statusDate', $payload['identifiers'][0]['channels']['email']);
        $this->assertEquals(array('test-tag'), $payload['tags']);
    }

    public function test_create_contact_omits_empty_tags(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":"contact-1"}'));

        $contact = new Contact();
        $contact->set_email('test@example.com');
        $this->client()->create_contact($contact);

        $payload = json_decode(WP_Http_Test_Stub::last_request()['args']['body'], true);
        $this->assertArrayNotHasKey('tags', $payload);
    }

    /**
     * @dataProvider error_response_provider
     */
    public function test_create_contact_reports_api_errors(int $status, string $error_code): void
    {
        $body = $status === 429 ? '{"detail":"problem","retryAfter":10}' : '{"detail":"problem"}';
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response($status, $body));

        $response = $this->client()->create_contact($this->contact());

        $this->assertEquals($error_code, $response->get_wp_error()->get_error_code());
        if ($status === 429) {
            $this->assertEquals(10, $response->get_wp_error()->get_error_data($error_code)['retryAfter']);
        }
    }

    public function test_create_contact_reports_empty_body(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, ''));

        $response = $this->client()->create_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_EMPTY_BODY, $response->get_wp_error()->get_error_code());
    }

    public function test_create_contact_reports_malformed_json(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":'));

        $response = $this->client()->create_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $response->get_wp_error()->get_error_code());
    }

    public function test_create_contact_preserves_transport_error(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 6: could not resolve host'));

        $contact = ContactFactory::create_contact(array('email' => 'test@example.com'));
        $response = $this->client()->create_contact($contact);

        $error = $response->get_wp_error();
        $this->assertEquals('http_request_failed', $error->get_error_code());
        $this->assertStringContainsString('could not resolve host', $error->get_error_message());
    }

    public function test_create_contact_reports_unauthorized(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(401, '{"title":"Unauthorized","detail":"Invalid API key."}'));

        $contact = ContactFactory::create_contact(array('email' => 'test@example.com'));
        $response = $this->client()->create_contact($contact);

        $error = $response->get_wp_error();
        $this->assertEquals(ApiResponse::ERROR_UNAUTHORIZED, $error->get_error_code());
        $this->assertStringContainsString('Invalid API key.', $error->get_error_message());
    }

    public function test_create_contact_reports_server_error(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(503, '{"title":"Unavailable"}'));

        $response = $this->client()->create_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_SERVER, $response->get_wp_error()->get_error_code());
    }

    public function test_save_contact_posts_new_contact_and_returns_id(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":"contact-1"}'));

        $response = $this->client()->save_contact($this->contact());

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('contact-1', $response->get_contact_id());
        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('POST', $request['method']);
        $this->assertEquals('https://api.omnisend.com/api/contacts', $request['url']);
        $this->assert_common_headers($request);
    }

    /**
     * @dataProvider error_response_provider
     */
    public function test_save_contact_reports_api_errors(int $status, string $error_code): void
    {
        $body = $status === 429 ? '{"detail":"problem","retryAfter":10}' : '{"detail":"problem"}';
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response($status, $body));

        $response = $this->client()->save_contact($this->contact());

        $this->assertEquals($error_code, $response->get_wp_error()->get_error_code());
        if ($status === 429) {
            $this->assertEquals(10, $response->get_wp_error()->get_error_data($error_code)['retryAfter']);
        }
    }

    public function test_save_contact_reports_empty_body(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, ''));

        $response = $this->client()->save_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_EMPTY_BODY, $response->get_wp_error()->get_error_code());
    }

    public function test_save_contact_reports_malformed_json(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"id":'));

        $response = $this->client()->save_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $response->get_wp_error()->get_error_code());
    }

    public function test_save_contact_preserves_transport_error(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 28: timeout'));

        $response = $this->client()->save_contact($this->contact());

        $this->assertEquals('http_request_failed', $response->get_wp_error()->get_error_code());
    }

    public function test_save_contact_reports_missing_id(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contactID":"contact-1"}'));

        $response = $this->client()->save_contact($this->contact());

        $this->assertEquals(ApiResponse::ERROR_UNEXPECTED_SHAPE, $response->get_wp_error()->get_error_code());
    }

    public function test_get_contact_by_email_returns_contact(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{"id":"contact-1","email":"test@example.com"}]}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('test@example.com', $response->get_contact()->get_email());
        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/contacts?email=test%40example.com', $request['url']);
        $this->assert_common_headers($request);
    }

    public function test_get_contact_by_email_encodes_email_query(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[]}'));

        $response = $this->client()->get_contact_by_email('first+tag@example.com');

        $this->assertEquals(ApiResponse::ERROR_NOT_FOUND, $response->get_wp_error()->get_error_code());
        $this->assertEquals(
            'https://api.omnisend.com/api/contacts?email=first%2Btag%40example.com',
            WP_Http_Test_Stub::last_request()['url']
        );
    }

    public function test_get_contact_by_email_reports_empty_contacts_as_not_found(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[],"paging":{}}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals(ApiResponse::ERROR_NOT_FOUND, $response->get_wp_error()->get_error_code());
        $this->assertEquals('Contact not found.', $response->get_wp_error()->get_error_message());
    }

    /**
     * @dataProvider error_response_provider
     */
    public function test_get_contact_by_email_reports_api_errors(int $status, string $error_code): void
    {
        $body = $status === 429 ? '{"detail":"problem","retryAfter":10}' : '{"detail":"problem"}';
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response($status, $body));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals($error_code, $response->get_wp_error()->get_error_code());
        if ($status === 429) {
            $this->assertEquals(10, $response->get_wp_error()->get_error_data($error_code)['retryAfter']);
        }
    }

    public function test_get_contact_by_email_preserves_transport_error(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 28: timeout'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $error = $response->get_wp_error();
        $this->assertEquals('http_request_failed', $error->get_error_code());
        $this->assertStringContainsString('timeout', $error->get_error_message());
    }

    public function test_get_contact_by_email_reports_retired_api_version(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(410, '{"title":"Gone","detail":"API version 2026-03-15 is retired."}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $error = $response->get_wp_error();
        $this->assertEquals(ApiResponse::ERROR_VERSION_RETIRED, $error->get_error_code());
        $this->assertStringContainsString('retired', $error->get_error_message());
    }

    public function test_get_contact_by_email_reports_rate_limiting(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(429, '{"title":"Too many requests","retryAfter":10}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $error = $response->get_wp_error();
        $this->assertEquals(ApiResponse::ERROR_RATE_LIMITED, $error->get_error_code());
        $this->assertEquals(10, $error->get_error_data(ApiResponse::ERROR_RATE_LIMITED)['retryAfter']);
    }

    public function test_get_contact_by_email_reports_malformed_json(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $response->get_wp_error()->get_error_code());
    }

    public function test_get_contact_by_email_reports_empty_body(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, ''));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals(ApiResponse::ERROR_EMPTY_BODY, $response->get_wp_error()->get_error_code());
    }

    public function test_get_contact_by_email_reports_unexpected_shape(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{}]}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals(ApiResponse::ERROR_UNEXPECTED_SHAPE, $response->get_wp_error()->get_error_code());
    }

    public function test_get_contact_by_email_reports_missing_status(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(0, ''));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals(ApiResponse::ERROR_TRANSPORT, $response->get_wp_error()->get_error_code());
    }

    public function test_delete_product_reports_forbidden_instead_of_success(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing products.write scope."}'));

        $response = $this->client()->delete_product_by_id('product-1');

        $error = $response->get_wp_error();
        $this->assertEquals(ApiResponse::ERROR_FORBIDDEN, $error->get_error_code());
        $this->assertStringContainsString('products.write', $error->get_error_message());
    }

    public function test_delete_product_accepts_empty_success_body(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(204, ''));

        $response = $this->client()->delete_product_by_id('product-1');

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertTrue($response->get_response());
    }

    public function test_delete_product_reports_transport_error(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 7: connection refused'));

        $response = $this->client()->delete_product_by_id('product-1');

        $this->assertEquals('http_request_failed', $response->get_wp_error()->get_error_code());
    }

    /**
     * @dataProvider error_response_provider
     */
    public function test_send_customer_event_reports_api_errors(int $status, string $error_code): void
    {
        $body = $status === 429 ? '{"detail":"problem","retryAfter":10}' : '{"detail":"problem"}';
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response($status, $body));

        $response = $this->client()->send_customer_event($this->event());

        $this->assertEquals($error_code, $response->get_wp_error()->get_error_code());
        if ($status === 429) {
            $this->assertEquals(10, $response->get_wp_error()->get_error_data($error_code)['retryAfter']);
        }
    }

    public function test_send_customer_event_accepts_bodyless_202(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(202, ''));

        $response = $this->client()->send_customer_event($this->event());

        $this->assertFalse($response->get_wp_error()->has_errors());
        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('POST', $request['method']);
        $this->assertEquals('https://api.omnisend.com/api/events', $request['url']);
        $this->assert_common_headers($request);
    }

    public function test_send_customer_event_sends_v5_contact_payload(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(202, ''));

        $contact = $this->contact();
        $contact->set_id('contact-1');
        $event = new Event();
        $event->set_custom_event_name('test-event');
        $event->set_contact($contact);

        $this->client()->send_customer_event($event);

        $payload = json_decode(WP_Http_Test_Stub::last_request()['args']['body'], true);
        $this->assertEquals('test-event', $payload['eventName']);
        $this->assertEquals($contact->to_array_for_event(), $payload['contact']);
        $this->assertEquals('contact-1', $payload['contact']['id']);
        $this->assertEquals('test@example.com', $payload['contact']['email']);
        $this->assertEquals('+37060000000', $payload['contact']['phone']);
    }

    public function test_send_customer_event_reports_malformed_json(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(202, '{"event":'));

        $response = $this->client()->send_customer_event($this->event());

        $this->assertEquals(ApiResponse::ERROR_INVALID_JSON, $response->get_wp_error()->get_error_code());
    }

    public function test_send_customer_event_preserves_transport_error(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 28: timeout'));

        $response = $this->client()->send_customer_event($this->event());

        $this->assertEquals('http_request_failed', $response->get_wp_error()->get_error_code());
    }
}

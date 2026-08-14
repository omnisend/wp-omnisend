<?php
namespace Omnisend\Internal\V1;

use Omnisend\Internal\ApiResponse;
use Omnisend\Internal\ContactFactory;
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

    public function test_create_contact_sends_versioned_headers_and_returns_id(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(201, '{"id":"contact-1"}'));

        $contact = ContactFactory::create_contact(array('email' => 'test@example.com'));
        $response = $this->client()->create_contact($contact);

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('contact-1', $response->get_contact_id());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/contacts', $request['url']);
        $this->assertEquals('Omnisend-API-Key brandid-secret', $request['args']['headers']['Authorization']);
        $this->assertEquals('2026-03-15', $request['args']['headers']['Omnisend-Version']);
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

    public function test_get_contact_by_email_returns_contact(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{"id":"contact-1","email":"test@example.com"}]}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertFalse($response->get_wp_error()->has_errors());
        $this->assertEquals('test@example.com', $response->get_contact()->get_email());
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
}

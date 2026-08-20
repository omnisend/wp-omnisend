<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class ConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
        $_POST = array(
            'action_nonce' => 'nonce',
            'api_key' => 'brandid-secret',
        );
    }

    protected function tearDown(): void
    {
        $_POST = array();
        $_GET = array();
    }

    private function connection_error(): string
    {
        $response = Connection::omnisend_post_connection();

        $this->assertFalse($response['success']);

        return $response['error'];
    }

    public function test_unauthorized_api_key(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(401, '{"title":"Unauthorized"}'));

        $this->assertEquals(
            'The API key was rejected by Omnisend. Check if the API key is correct.',
            $this->connection_error()
        );
    }

    public function test_missing_permissions_is_not_reported_as_invalid_api_key(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing brands.read scope."}'));

        $error = $this->connection_error();

        $this->assertStringContainsString('missing required permissions (brands.read)', $error);
        $this->assertStringContainsString('paste it here to reconnect', $error);
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_missing_permissions_leaves_the_connection_form_available_for_another_key(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing brands.read scope."}'));

        $this->connection_error();

        $_GET = array(
            'action' => 'show_connection_form',
            '_wpnonce' => 'nonce',
        );

        $this->assertTrue(Connection::show_connection_view());
        $this->assertFalse(Connection::show_connected_store_view());
    }

    public function test_retired_api_version(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(410, '{"title":"Gone"}'));

        $this->assertStringContainsString('retired Omnisend API version', $this->connection_error());
    }

    public function test_rate_limited(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(429, '{"title":"Too many requests","retryAfter":30}'));

        $this->assertStringContainsString('rate limiting', $this->connection_error());
    }

    public function test_network_failure(): void
    {
        WP_Http_Test_Stub::queue(new WP_Error('http_request_failed', 'cURL error 28: timeout'));

        $error = $this->connection_error();
        $this->assertStringContainsString('Could not reach Omnisend API', $error);
        $this->assertStringContainsString('timeout', $error);
    }

    public function test_server_error(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(503, ''));

        $this->assertStringContainsString('temporarily unavailable', $this->connection_error());
    }

    public function test_malformed_json(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":'));

        $this->assertStringContainsString('unexpected response', $this->connection_error());
    }

    public function test_empty_body(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, ''));

        $this->assertStringContainsString('unexpected response', $this->connection_error());
    }

    public function test_missing_brand_id(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"platform":""}'));

        $this->assertStringContainsString('did not return a brand for this API key', $this->connection_error());
    }

    public function test_missing_platform_field(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1"}'));

        $this->assertStringContainsString('platform not found in response.', $this->connection_error());
    }

    public function test_already_connected_to_other_platform(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":"shopify","connected":true}'));

        $this->assertStringContainsString('already connected to non-WordPress site', $this->connection_error());
    }

    public function test_already_connected_wordpress_store_succeeds(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":"wordpress"}'));

        $response = Connection::omnisend_post_connection();

        $this->assertTrue($response['success']);
        $this->assertEquals('brand-1', Options::get_brand_id());
        $this->assertTrue(Options::is_store_connected());
    }

    public function test_store_connect_failure_reports_api_error(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":""}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"This action requires the \'accounts.write\' permission."}'));

        $this->assertStringContainsString('missing required permissions (accounts.write)', $this->connection_error());
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_store_connect_success(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":""}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"connected":true}'));

        $response = Connection::omnisend_post_connection();

        $this->assertTrue($response['success']);
        $this->assertTrue(Options::is_store_connected());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('Omnisend-API-Key brandid-secret', $request['args']['headers']['Authorization']);
        $this->assertEquals('2026-03-15', $request['args']['headers']['Omnisend-Version']);
    }

    public function test_store_connect_without_connected_flag_reports_unexpected_shape(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":""}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{}'));

        $this->assertStringContainsString('connected not found in response.', $this->connection_error());
    }
}

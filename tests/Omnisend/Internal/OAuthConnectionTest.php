<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Http_Test_Stub;
use WP_Redirect_Test_Exception;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class OAuthConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
        $GLOBALS['wp_test_nonce_valid'] = true;
        $_GET = array();
    }

    protected function tearDown(): void
    {
        $_GET = array();
        unset($GLOBALS['wp_test_nonce_valid']);
    }

    private function registration_response(): array
    {
        return WP_Http_Test_Stub::response(201, '{"client_id":"client-1","client_secret":"secret-1"}');
    }

    private function token_response(string $access_token = 'access-1', string $refresh_token = 'refresh-1', int $expires_in = 2592000): array
    {
        return WP_Http_Test_Stub::response(200, json_encode(array(
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => $expires_in,
        )));
    }

    /**
     * Production code exits after redirecting, so the redirect surfaces as an exception in tests.
     */
    private function handle_oauth_request(): void
    {
        try {
            Connection::handle_oauth_request();
        } catch (WP_Redirect_Test_Exception $exception) {
            return;
        }

        $this->fail('Handling an OAuth request did not redirect.');
    }

    private function start_connect(): void
    {
        $_GET = array('omnisend_oauth' => 'connect', '_wpnonce' => 'nonce');
        $this->handle_oauth_request();
    }

    private function complete_callback(): void
    {
        $_GET = array(
            'page' => 'omnisend',
            'code' => 'auth-code',
            'state' => $this->pending_state(),
        );
        $this->handle_oauth_request();
    }

    private function pending_state(): string
    {
        $state = get_transient('omni_send_core_oauth_state');

        return is_string($state) ? $state : '';
    }

    private function last_redirect(): string
    {
        $redirects = $GLOBALS['wp_test_redirects'];

        return $redirects ? $redirects[count($redirects) - 1] : '';
    }

    private function oauth_error(): string
    {
        $error = get_transient('omni_send_core_oauth_error');

        return is_string($error) ? $error : '';
    }

    /**
     * @param array|\WP_Error $brand_response Response the brand read of the callback gets from Omnisend.
     *
     * @return string Message shown to the administrator after the callback.
     */
    private function callback_error_for_brand_response($brand_response): string
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        WP_Http_Test_Stub::queue($this->token_response());
        WP_Http_Test_Stub::queue($brand_response);

        $this->complete_callback();

        $this->assertFalse(Options::is_store_connected());

        return $this->oauth_error();
    }

    public function test_connect_registers_the_client_and_redirects_to_the_consent_screen(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());

        $this->start_connect();

        $registration = WP_Http_Test_Stub::$requests[0];
        $this->assertEquals('https://app.omnisend.com/oauth2/register', $registration['url']);

        $body = json_decode($registration['args']['body'], true);
        $this->assertEquals('wordpress', $body['client_name']);
        $this->assertEquals(array('authorization_code', 'refresh_token'), $body['grant_types']);
        $this->assertEquals(
            array('https://example.com/wp-admin/admin.php?page=omnisend'),
            $body['redirect_uris']
        );

        $this->assertEquals('client-1', Options::get_oauth_client_id());

        $redirect = $this->last_redirect();
        $this->assertStringStartsWith('https://app.omnisend.com/oauth2/authorize?', $redirect);
        $this->assertStringContainsString('client_id=client-1', $redirect);
        $this->assertStringContainsString('response_type=code', $redirect);
        $this->assertStringContainsString('brands.write', urldecode($redirect));
        $this->assertStringContainsString('state=' . $this->pending_state(), $redirect);
    }

    public function test_authorize_url_carries_the_encoded_redirect_uri(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());

        $this->start_connect();

        $redirect = $this->last_redirect();
        $this->assertStringContainsString(
            'redirect_uri=https%3A%2F%2Fexample.com%2Fwp-admin%2Fadmin.php%3Fpage%3Domnisend',
            $redirect
        );

        $query = array();
        parse_str(parse_url($redirect, PHP_URL_QUERY), $query);
        $this->assertArrayNotHasKey('page', $query);
        $this->assertArrayNotHasKey('omnisend_oauth', $query);
        $this->assertEquals('https://example.com/wp-admin/admin.php?page=omnisend', $query['redirect_uri']);
    }

    public function test_denied_authorization_is_reported(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        $_GET = array('page' => 'omnisend', 'error' => 'access_denied');
        $this->handle_oauth_request();

        $this->assertStringContainsString('access_denied', $this->oauth_error());
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_oauth_issuer_is_allowed_as_a_redirect_host(): void
    {
        $this->assertEquals(
            array('example.com', 'app.omnisend.com'),
            Connection::allow_oauth_issuer_redirect(array('example.com'))
        );
    }

    public function test_authorization_response_without_a_pending_attempt_is_reported(): void
    {
        $_GET = array(
            'page' => 'omnisend',
            'code' => 'auth-code',
            'state' => 'state-of-an-expired-attempt',
        );
        $this->handle_oauth_request();

        $this->assertStringContainsString('did not match this site', $this->oauth_error());
        $this->assertEmpty(WP_Http_Test_Stub::$requests);
    }

    public function test_settings_page_without_an_authorization_response_is_not_treated_as_a_callback(): void
    {
        $_GET = array('page' => 'omnisend');

        Connection::handle_oauth_request();

        $this->assertEmpty($GLOBALS['wp_test_redirects']);
        $this->assertEmpty(WP_Http_Test_Stub::$requests);
    }

    public function test_registration_failure_is_reported_and_does_not_start_the_flow(): void
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(400, '{"title":"invalid_client_metadata"}'));

        $this->start_connect();

        $this->assertStringContainsString('did not go through', $this->oauth_error());
        $this->assertEquals('', Options::get_oauth_client_id());
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_callback_exchanges_the_code_and_connects_the_store_with_the_access_token(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        WP_Http_Test_Stub::queue($this->token_response());
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":""}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1"}'));

        $this->complete_callback();

        $this->assertEquals('', $this->oauth_error());
        $this->assertTrue(Options::is_store_connected());
        $this->assertEquals('brand-1', Options::get_brand_id());
        $this->assertEquals(Options::AUTH_MODE_OAUTH, Options::get_auth_mode());
        $this->assertEquals('', Options::get_api_key());

        $token_request = WP_Http_Test_Stub::$requests[1];
        $this->assertEquals('https://app.omnisend.com/oauth2/token', $token_request['url']);
        $this->assertEquals('authorization_code', $token_request['args']['body']['grant_type']);
        $this->assertEquals('auth-code', $token_request['args']['body']['code']);
        $this->assertEquals('secret-1', $token_request['args']['body']['client_secret']);
        $this->assertEquals(
            'https://example.com/wp-admin/admin.php?page=omnisend',
            $token_request['args']['body']['redirect_uri']
        );

        $connect_request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/brands/current', $connect_request['url']);
        $this->assertEquals('POST', $connect_request['method']);
        $this->assertEquals('Bearer access-1', $connect_request['args']['headers']['Authorization']);
        $this->assertEquals('2026-03-15', $connect_request['args']['headers']['Omnisend-Version']);
    }

    public function test_callback_with_mismatched_state_does_not_exchange_the_code(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        $requests_before = count(WP_Http_Test_Stub::$requests);

        $_GET = array(
            'page' => 'omnisend',
            'code' => 'auth-code',
            'state' => 'not-the-state-we-sent',
        );
        $this->handle_oauth_request();

        $this->assertStringContainsString('did not match this site', $this->oauth_error());
        $this->assertCount($requests_before, WP_Http_Test_Stub::$requests);
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_connect_with_failed_nonce_verification_does_not_start_the_flow(): void
    {
        $GLOBALS['wp_test_nonce_valid'] = false;

        $_GET = array('omnisend_oauth' => 'connect', '_wpnonce' => 'nonce');
        $this->handle_oauth_request();

        $this->assertStringContainsString('could not be verified', $this->oauth_error());
        $this->assertEmpty(WP_Http_Test_Stub::$requests);
    }

    public function test_brand_of_another_platform_is_not_connected(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        WP_Http_Test_Stub::queue($this->token_response());
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":"shopify"}'));

        $this->complete_callback();

        $this->assertStringContainsString('connected to another platform (shopify)', $this->oauth_error());
        $this->assertFalse(Options::is_store_connected());
    }

    public function test_rejected_brand_write_leaves_the_store_disconnected(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        WP_Http_Test_Stub::queue($this->token_response());
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1","platform":""}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing brands.write scope."}'));

        $this->complete_callback();

        $this->assertStringContainsString('missing permissions (brands.write)', $this->oauth_error());
        $this->assertFalse(Options::is_store_connected());
        $this->assertEquals('', Options::get_oauth_access_token());
    }

    public function test_token_response_without_access_token_is_rejected(): void
    {
        WP_Http_Test_Stub::queue($this->registration_response());
        $this->start_connect();

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"refresh_token":"refresh-1","expires_in":3600}'));

        $this->complete_callback();

        $this->assertStringContainsString('access_token not found in response.', $this->oauth_error());
        $this->assertEquals('', Options::get_oauth_access_token());
    }

    public function test_rejected_credential_on_the_brand_read_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(401, '{"title":"Unauthorized"}'));

        $this->assertStringContainsString('rejected by Omnisend', $error);
    }

    public function test_brand_read_without_permission_asks_to_grant_access_again(): void
    {
        $error = $this->callback_error_for_brand_response(
            WP_Http_Test_Stub::response(403, '{"title":"Forbidden","detail":"Missing brands.read scope."}')
        );

        $this->assertStringContainsString('missing permissions (brands.read)', $error);
        $this->assertStringContainsString('connect again', $error);
    }

    public function test_retired_api_version_on_the_brand_read_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(410, '{"title":"Gone"}'));

        $this->assertStringContainsString('retired Omnisend API version', $error);
    }

    public function test_rate_limited_brand_read_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(
            WP_Http_Test_Stub::response(429, '{"title":"Too many requests","retryAfter":30}')
        );

        $this->assertStringContainsString('rate limiting', $error);
    }

    public function test_network_failure_on_the_brand_read_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(new WP_Error('http_request_failed', 'cURL error 28: timeout'));

        $this->assertStringContainsString('Could not reach Omnisend API', $error);
        $this->assertStringContainsString('timeout', $error);
    }

    public function test_server_error_on_the_brand_read_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(503, ''));

        $this->assertStringContainsString('temporarily unavailable', $error);
    }

    public function test_empty_brand_read_body_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(200, ''));

        $this->assertStringContainsString('unexpected response', $error);
    }

    public function test_malformed_brand_read_body_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(200, '{"brandID":'));

        $this->assertStringContainsString('unexpected response', $error);
    }

    public function test_brand_read_of_an_unexpected_shape_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(200, '["brand-1"]'));

        $this->assertStringContainsString('unexpected response', $error);
    }

    public function test_brand_read_without_a_brand_id_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(200, '{"platform":""}'));

        $this->assertStringContainsString('brandID not found in response.', $error);
    }

    public function test_brand_read_without_a_platform_is_reported(): void
    {
        $error = $this->callback_error_for_brand_response(WP_Http_Test_Stub::response(200, '{"brandID":"brand-1"}'));

        $this->assertStringContainsString('platform not found in response.', $error);
    }
}

<?php
namespace Omnisend\Internal;

use Omnisend\Internal\V1\Client;
use Omnisend\SDK\V1\Contact;
use Omnisend\SDK\V1\Omnisend;
use PHPUnit\Framework\TestCase;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class AuthModeTest extends TestCase
{
    private const API_KEY = 'brandid-secret';

    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
    }

    private function client(): Client
    {
        return new Client(Options::get_api_key(), 'test-plugin', '1.0.0', null);
    }

    private function connect_with_api_key(): void
    {
        Options::set_api_key(self::API_KEY);
        Options::set_brand_id('brandid');
        Options::set_store_connected();
    }

    private function connect_with_oauth(int $expires_in): void
    {
        Options::set_brand_id('brand-1');
        Options::set_store_connected();
        Options::set_oauth_client('client-1', 'secret-1', 'https://example.com/wp-admin/admin.php?page=omnisend');
        Options::set_oauth_tokens('access-1', 'refresh-1', time() + $expires_in);
    }

    private function request_contact(): array
    {
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{"id":"contact-1","email":"test@example.com"}]}'));

        $this->client()->get_contact_by_email('test@example.com');

        return WP_Http_Test_Stub::last_request();
    }

    public function test_install_connected_before_oauth_existed_keeps_using_its_api_key(): void
    {
        $this->connect_with_api_key();

        $this->assertEquals(Options::AUTH_MODE_API_KEY, Options::get_auth_mode());
        $this->assertTrue(Options::is_connected());
        $this->assertTrue(Omnisend::is_connected());

        $request = $this->request_contact();

        $this->assertEquals('Omnisend-API-Key brandid-secret', $request['args']['headers']['Authorization']);
        $this->assertCount(1, WP_Http_Test_Stub::$requests);
    }

    public function test_registered_oauth_client_alone_does_not_switch_an_api_key_install_to_oauth(): void
    {
        $this->connect_with_api_key();
        Options::set_oauth_client('client-1', 'secret-1', 'https://example.com/wp-admin/admin.php?page=omnisend');

        $this->assertEquals(Options::AUTH_MODE_API_KEY, Options::get_auth_mode());
        $this->assertEquals('Omnisend-API-Key brandid-secret', $this->request_contact()['args']['headers']['Authorization']);
    }

    public function test_oauth_connection_authenticates_with_the_access_token(): void
    {
        $this->connect_with_oauth(DAY_IN_SECONDS);

        $this->assertEquals(Options::AUTH_MODE_OAUTH, Options::get_auth_mode());
        $this->assertTrue(Omnisend::is_connected());

        $request = $this->request_contact();

        $this->assertEquals('Bearer access-1', $request['args']['headers']['Authorization']);
        $this->assertCount(1, WP_Http_Test_Stub::$requests);
    }

    public function test_expired_access_token_is_refreshed_before_the_request(): void
    {
        $this->connect_with_oauth(-DAY_IN_SECONDS);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"access_token":"access-2","refresh_token":"refresh-2","expires_in":3600}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{"id":"contact-1","email":"test@example.com"}]}'));

        $response = $this->client()->get_contact_by_email('test@example.com');

        $this->assertFalse($response->get_wp_error()->has_errors());

        $refresh = WP_Http_Test_Stub::$requests[0];
        $this->assertEquals('https://app.omnisend.com/oauth2/token', $refresh['url']);
        $this->assertEquals('refresh_token', $refresh['args']['body']['grant_type']);
        $this->assertEquals('refresh-1', $refresh['args']['body']['refresh_token']);

        $this->assertEquals('Bearer access-2', WP_Http_Test_Stub::last_request()['args']['headers']['Authorization']);
        $this->assertEquals('access-2', Options::get_oauth_access_token());
        $this->assertEquals('refresh-2', Options::get_oauth_refresh_token());
    }

    public function test_refresh_response_without_a_new_refresh_token_keeps_the_current_one(): void
    {
        $this->connect_with_oauth(-DAY_IN_SECONDS);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"access_token":"access-2","expires_in":3600}'));
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"contacts":[{"id":"contact-1","email":"test@example.com"}]}'));

        $this->client()->get_contact_by_email('test@example.com');

        $this->assertEquals('refresh-1', Options::get_oauth_refresh_token());
    }

    public function test_failed_refresh_is_reported_and_never_falls_back_to_an_api_key(): void
    {
        $this->connect_with_oauth(-DAY_IN_SECONDS);
        Options::set_api_key(self::API_KEY);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(400, '{"error":"invalid_grant"}'));

        $contact = new Contact();
        $contact->set_email('test@example.com');

        $error = $this->client()->save_contact($contact)->get_wp_error();

        $this->assertTrue($error->has_errors());
        $this->assertContains(OAuthClient::ERROR_REFRESH, $error->get_error_codes());
        $this->assertCount(1, WP_Http_Test_Stub::$requests);
        $this->assertEquals('https://app.omnisend.com/oauth2/token', WP_Http_Test_Stub::last_request()['url']);
    }

    public function test_sdk_connect_store_of_an_api_key_install_uses_the_retained_v3_account_write(): void
    {
        $this->connect_with_api_key();

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"verified":true}'));

        $response = $this->client()->connect_store('wordpress');

        $this->assertFalse($response->get_wp_error()->has_errors());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/v3/accounts/brandid', $request['url']);
        $this->assertEquals('brandid-secret', $request['args']['headers']['X-API-Key']);
        $this->assertArrayNotHasKey('Authorization', $request['args']['headers']);
        $this->assertEquals('test-plugin', $request['args']['headers']['X-INTEGRATION-NAME']);
    }

    public function test_sdk_connect_store_of_an_oauth_install_writes_the_current_brand(): void
    {
        $this->connect_with_oauth(DAY_IN_SECONDS);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{}'));

        $response = $this->client()->connect_store('wordpress');

        $this->assertFalse($response->get_wp_error()->has_errors());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/brands/current', $request['url']);
        $this->assertEquals('Bearer access-1', $request['args']['headers']['Authorization']);
        $this->assertEquals('2026-03-15', $request['args']['headers']['Omnisend-Version']);
    }

    public function test_store_without_any_credential_is_not_connected(): void
    {
        $this->assertEquals('', Options::get_auth_mode());
        $this->assertFalse(Options::is_connected());
        $this->assertFalse(Omnisend::is_connected());
    }
}

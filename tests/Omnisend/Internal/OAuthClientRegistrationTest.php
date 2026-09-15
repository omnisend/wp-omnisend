<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class OAuthClientRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
    }

    private function registration_response(): array
    {
        return WP_Http_Test_Stub::response(201, '{"client_id":"client-2","client_secret":"secret-2"}');
    }

    public function test_registers_a_new_client_when_the_previous_attempt_never_finished(): void
    {
        Options::set_oauth_client('client-1', 'secret-1');
        WP_Http_Test_Stub::queue($this->registration_response());

        $url = OAuthClient::get_authorization_url();

        $registration = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://app.omnisend.com/oauth2/register', $registration['url']);
        $this->assertEquals(
            array('https://example.com/wp-admin/admin.php?page=omnisend'),
            json_decode($registration['args']['body'], true)['redirect_uris']
        );

        $this->assertEquals('client-2', Options::get_oauth_client_id());
        $this->assertEquals('secret-2', Options::get_oauth_client_secret());
        $this->assertStringContainsString('client_id=client-2', $url);
    }

    public function test_registers_a_new_client_after_a_failed_callback_cleared_the_tokens(): void
    {
        Options::set_oauth_client('client-1', 'secret-1');
        Options::set_oauth_tokens('access-1', 'refresh-1', time() + 3600);
        Options::clear_oauth_tokens();
        WP_Http_Test_Stub::queue($this->registration_response());

        OAuthClient::get_authorization_url();

        $this->assertCount(1, WP_Http_Test_Stub::$requests);
        $this->assertEquals('client-2', Options::get_oauth_client_id());
    }

    public function test_keeps_the_client_and_tokens_of_a_connected_store(): void
    {
        Options::set_oauth_client('client-1', 'secret-1');
        Options::set_oauth_tokens('access-1', 'refresh-1', time() + 3600);

        $url = OAuthClient::get_authorization_url();

        $this->assertEmpty(WP_Http_Test_Stub::$requests);
        $this->assertEquals('client-1', Options::get_oauth_client_id());
        $this->assertEquals('secret-1', Options::get_oauth_client_secret());
        $this->assertEquals('access-1', Options::get_oauth_access_token());
        $this->assertEquals('refresh-1', Options::get_oauth_refresh_token());
        $this->assertStringContainsString('client_id=client-1', $url);
    }

    public function test_registration_failure_keeps_the_previous_client(): void
    {
        Options::set_oauth_client('client-1', 'secret-1');
        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(400, '{"title":"invalid_client_metadata"}'));

        $result = OAuthClient::get_authorization_url();

        $this->assertTrue(is_wp_error($result));
        $this->assertEquals('client-1', Options::get_oauth_client_id());
    }
}

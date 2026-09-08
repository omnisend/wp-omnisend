<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class OAuthClientRegistrationTest extends TestCase
{
    private const CURRENT_REDIRECT_URI = 'https://example.com/wp-admin/admin.php?page=omnisend';
    private const OLD_REDIRECT_URI     = 'https://old.example.com/wp-admin/admin.php?page=omnisend';

    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
    }

    private function registration_response(): array
    {
        return WP_Http_Test_Stub::response(201, '{"client_id":"client-2","client_secret":"secret-2"}');
    }

    public function test_registers_a_new_client_when_the_redirect_uri_changed(): void
    {
        Options::set_oauth_client('client-1', 'secret-1', self::OLD_REDIRECT_URI);
        Options::set_oauth_tokens('access-1', 'refresh-1', time() + 3600);
        WP_Http_Test_Stub::queue($this->registration_response());

        $url = OAuthClient::get_authorization_url();

        $registration = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://app.omnisend.com/oauth2/register', $registration['url']);
        $this->assertEquals(
            array(self::CURRENT_REDIRECT_URI),
            json_decode($registration['args']['body'], true)['redirect_uris']
        );

        $this->assertEquals('client-2', Options::get_oauth_client_id());
        $this->assertEquals('secret-2', Options::get_oauth_client_secret());
        $this->assertEquals(self::CURRENT_REDIRECT_URI, Options::get_oauth_client_redirect_uri());
        $this->assertStringContainsString('client_id=client-2', $url);

        $this->assertEquals('access-1', Options::get_oauth_access_token());
        $this->assertEquals('refresh-1', Options::get_oauth_refresh_token());
    }

    public function test_does_not_register_when_the_redirect_uri_is_unchanged(): void
    {
        Options::set_oauth_client('client-1', 'secret-1', self::CURRENT_REDIRECT_URI);

        $url = OAuthClient::get_authorization_url();

        $this->assertEmpty(WP_Http_Test_Stub::$requests);
        $this->assertEquals('client-1', Options::get_oauth_client_id());
        $this->assertStringContainsString('client_id=client-1', $url);
    }

    public function test_client_without_a_stored_redirect_uri_is_kept_and_the_uri_is_backfilled(): void
    {
        update_option('omni_send_core_oauth_client_id', 'client-1');
        update_option('omni_send_core_oauth_client_secret', 'secret-1');

        $url = OAuthClient::get_authorization_url();

        $this->assertEmpty(WP_Http_Test_Stub::$requests);
        $this->assertEquals('client-1', Options::get_oauth_client_id());
        $this->assertEquals(self::CURRENT_REDIRECT_URI, Options::get_oauth_client_redirect_uri());
        $this->assertStringContainsString('client_id=client-1', $url);
    }

    public function test_delete_all_removes_the_registered_redirect_uri(): void
    {
        Options::set_oauth_client('client-1', 'secret-1', self::CURRENT_REDIRECT_URI);

        Options::delete_all();

        $this->assertEquals('', Options::get_oauth_client_id());
        $this->assertEquals('', Options::get_oauth_client_secret());
        $this->assertEquals('', Options::get_oauth_client_redirect_uri());
        $this->assertArrayNotHasKey('omni_send_core_oauth_client_redirect_uri', $GLOBALS['wp_test_options']);
    }

    public function test_disconnect_keeps_the_registered_client(): void
    {
        Options::set_oauth_client('client-1', 'secret-1', self::CURRENT_REDIRECT_URI);

        Options::disconnect();

        $this->assertEquals('client-1', Options::get_oauth_client_id());
        $this->assertEquals(self::CURRENT_REDIRECT_URI, Options::get_oauth_client_redirect_uri());
    }
}

<?php
namespace Omnisend\Internal;

use PHPUnit\Framework\TestCase;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

final class WooPluginConnectionTest extends TestCase
{
    private const WOO_PLUGIN_API_KEY = 'brandid-secret';

    protected function setUp(): void
    {
        WP_Http_Test_Stub::reset();
        wp_test_reset_options();
    }

    public function test_unconnected_store_adopts_the_woo_plugin_api_key(): void
    {
        $GLOBALS['wp_test_woocommerce_plugin_active'] = true;
        update_option(OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION, self::WOO_PLUGIN_API_KEY);

        WP_Http_Test_Stub::queue(WP_Http_Test_Stub::response(200, '{"brandID":"brandid","platform":"wordpress"}'));

        Connection::connect_with_omnisend_for_woo_plugin();

        $this->assertTrue(Options::is_store_connected());
        $this->assertEquals('brandid', Options::get_brand_id());
        $this->assertEquals(self::WOO_PLUGIN_API_KEY, Options::get_api_key());
        $this->assertEquals(Options::AUTH_MODE_API_KEY, Options::get_auth_mode());

        $request = WP_Http_Test_Stub::last_request();
        $this->assertEquals('https://api.omnisend.com/api/brands/current', $request['url']);
        $this->assertEquals('GET', $request['method']);
        $this->assertEquals('Omnisend-API-Key ' . self::WOO_PLUGIN_API_KEY, $request['args']['headers']['Authorization']);
    }

    public function test_store_without_the_woo_plugin_is_left_unconnected(): void
    {
        update_option(OMNISEND_CORE_WOOCOMMERCE_PLUGIN_API_KEY_OPTION, self::WOO_PLUGIN_API_KEY);

        Connection::connect_with_omnisend_for_woo_plugin();

        $this->assertFalse(Options::is_store_connected());
        $this->assertEmpty(WP_Http_Test_Stub::$requests);
    }
}

<?php

namespace Omnisend\Tests\Unit\Internal;

use Omnisend\Internal\Connection;
use PHPUnit\Framework\TestCase;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

if ( ! defined( 'OMNISEND_CORE_PLUGIN_VERSION' ) ) {
	define( 'OMNISEND_CORE_PLUGIN_VERSION', '1.8.1' );
}

final class ConnectionTest extends TestCase
{
	private $default_landing_page_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=landing_page';

	protected function setUp(): void
	{
		parent::setUp();

		Connection::$landing_page_url = $this->default_landing_page_url;
		$GLOBALS['omnisend_test_http_response'] = array(
			'body'     => '',
			'response' => array(
				'code' => 200,
			),
		);
	}

	protected function tearDown(): void
	{
		Connection::$landing_page_url = $this->default_landing_page_url;
		unset( $GLOBALS['omnisend_test_http_response'] );

		parent::tearDown();
	}

	public function test_landing_page_url_is_applied_from_wordpress_settings(): void
	{
		$landing_page_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=explore';
		$GLOBALS['omnisend_test_http_response'] = array(
			'body'     => json_encode(
				array(
					'exploreOmnisendLink' => $landing_page_url,
				)
			),
			'response' => array(
				'code' => 200,
			),
		);

		Connection::resolve_wordpress_settings();

		$this->assertSame( $landing_page_url, Connection::$landing_page_url );
	}

	public function test_wp_error_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response'] = new \WP_Error( 'request_failed' );

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_http_error_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response'] = array(
			'body'     => '{"exploreOmnisendLink":"https://app.omnisend.com/explore"}',
			'response' => array(
				'code' => 500,
			),
		);

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_empty_body_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response']['body'] = '';

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_malformed_json_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response']['body'] = '{malformed';

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_missing_explore_link_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response']['body'] = '{"otherSetting":"value"}';

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_non_omnisend_domain_keeps_default_landing_page_url(): void
	{
		$GLOBALS['omnisend_test_http_response']['body'] = '{"exploreOmnisendLink":"https://example.com/explore"}';

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}
}

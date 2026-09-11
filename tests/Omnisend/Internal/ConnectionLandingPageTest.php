<?php

namespace Omnisend\Tests\Unit\Internal;

use Omnisend\Internal\Connection;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_Http_Test_Stub;

require_once( __DIR__ . '/../../dependencies/dependencies.php' );

if ( ! defined( 'OMNISEND_CORE_PLUGIN_VERSION' ) ) {
	define( 'OMNISEND_CORE_PLUGIN_VERSION', '1.9.0' );
}

final class ConnectionLandingPageTest extends TestCase
{
	private $default_landing_page_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=landing_page';

	protected function setUp(): void
	{
		parent::setUp();

		WP_Http_Test_Stub::reset();
		Connection::$landing_page_url = $this->default_landing_page_url;
	}

	protected function tearDown(): void
	{
		Connection::$landing_page_url = $this->default_landing_page_url;

		parent::tearDown();
	}

	public function test_landing_page_url_is_applied_from_wordpress_settings(): void
	{
		$landing_page_url = 'https://app.omnisend.com/registrationv2?utm_source=wordpress_plugin&utm_content=explore';
		WP_Http_Test_Stub::queue(
			WP_Http_Test_Stub::response( 200, json_encode( array( 'exploreOmnisendLink' => $landing_page_url ) ) )
		);

		Connection::resolve_wordpress_settings();

		$this->assertSame( $landing_page_url, Connection::$landing_page_url );
	}

	public function test_wp_error_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue( new WP_Error( 'request_failed' ) );

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_http_error_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue(
			WP_Http_Test_Stub::response( 500, '{"exploreOmnisendLink":"https://app.omnisend.com/explore"}' )
		);

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_empty_body_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue( WP_Http_Test_Stub::response( 200, '' ) );

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_malformed_json_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue( WP_Http_Test_Stub::response( 200, '{malformed' ) );

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_missing_explore_link_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue( WP_Http_Test_Stub::response( 200, '{"otherSetting":"value"}' ) );

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}

	public function test_non_omnisend_domain_keeps_default_landing_page_url(): void
	{
		WP_Http_Test_Stub::queue(
			WP_Http_Test_Stub::response( 200, '{"exploreOmnisendLink":"https://example.com/explore"}' )
		);

		Connection::resolve_wordpress_settings();

		$this->assertSame( $this->default_landing_page_url, Connection::$landing_page_url );
	}
}
